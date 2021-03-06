<?php

namespace OGIVE\AlertBundle\Services;

use Doctrine\ORM\EntityManager;

/**
 * Description of MailService
 *
 * @author Eric TONYE
 */
class CommonService {

    protected $mail_service;
    protected $sms_service;
    protected $em;

    public function __construct(\OGIVE\AlertBundle\Services\MailService $mail_service, \OGIVE\AlertBundle\Services\SmsService $sms_service, EntityManager $em) {
        $this->mail_service = $mail_service;
        $this->sms_service = $sms_service;
        $this->em = $em;
    }

    public function sendSubscriptionConfirmation(\OGIVE\AlertBundle\Entity\Subscriber $subscriber) {
        $historiqueAlertSubscriber = new \OGIVE\AlertBundle\Entity\HistoricalAlertSubscriber();
        $repositoryHistorique = $this->em->getRepository('OGIVEAlertBundle:HistoricalAlertSubscriber');
        $cout = "";
        if ($subscriber->getSubscription()->getPeriodicity() === 1) {
            $cout = $subscriber->getSubscription()->getPrice() . " " . $subscriber->getSubscription()->getCurrency() . ", validité = 1 an";
        } elseif ($subscriber->getSubscription()->getPeriodicity() === 2) {
            $cout = $subscriber->getSubscription()->getPrice() . " " . $subscriber->getSubscription()->getCurrency() . ", validité = 6 mois";
        } elseif ($subscriber->getSubscription()->getPeriodicity() === 3) {
            $cout = $subscriber->getSubscription()->getPrice() . " " . $subscriber->getSubscription()->getCurrency() . ", validité = 3 mois";
        } elseif ($subscriber->getSubscription()->getPeriodicity() === 4) {
            $cout = $subscriber->getSubscription()->getPrice() . " " . $subscriber->getSubscription()->getCurrency() . ", validité = 1 mois";
        } elseif ($subscriber->getSubscription()->getPeriodicity() === 4) {
            $cout = $subscriber->getSubscription()->getPrice() . " " . $subscriber->getSubscription()->getCurrency() . ", validité = 1 semaine";
        }
        $content = $subscriber->getEntreprise()->getName() . ", votre souscription au service <<Appels d'offres Infos>> a été éffectuée avec succès. \nCoût du forfait = " . $cout . ". \nOGIVE SOLUTIONS vous remercie pour votre confiance.";
        //$this->sms_service->sendSms($subscriber->getPhoneNumber(), $content);
        $this->mail_service->sendMail($subscriber->getEmail(), "CONFIRMATION DE L'ABONNEMENT", $content);
        $historiqueAlertSubscriber->setMessage($content);
        $historiqueAlertSubscriber->setSubscriber($subscriber);
        $historiqueAlertSubscriber->setAlertType("SMS_CONFIRMATION_SUBSCRIPTION");
        return $repositoryHistorique->saveHistoricalAlertSubscriber($historiqueAlertSubscriber);
    }

    //get string date as dd/mm/yy
    public function getStringDateForSms($date) {
        return date("d", strtotime($date)) . "/" . date("m", strtotime($date)) . "/" . substr(date("Y", strtotime($date)), -2);
    }

    //Nomber of procedure by owner
    public function getStatisticsOfProceduresByOwner($start_date, $end_date) {
        set_include_path(get_include_path() . PATH_SEPARATOR . "..");
        include_once("xlsxwriter.class.php");
        $writer = new \XLSXWriter();
        $writer->setAuthor('SI OGIVE');
        $writer->setTempDir(sys_get_temp_dir()); //set custom tempdir
        $owners = $this->em->getRepository('OGIVEAlertBundle:Owner')->findBy(array("state" => 1, "status" => 1));
        $domains = $this->em->getRepository('OGIVEAlertBundle:Domain')->findBy(array("state" => 1, "status" => 1));
        $header = array("Maître d'ouvrage" => "string", "AAO" => "string", "ASMI" => "string", "Additifs" => "string", "Attributions" => "string", "Toutes les offres" => "string");
        $sheet1 = "M.O";
        $rows = array();
        $owner = new \OGIVE\AlertBundle\Entity\Owner();
        foreach ($owners as $owner) {
            $total_aao = count($this->em->getRepository('OGIVEAlertBundle:CallOffer')->getAllByQueriedParameters(null, $start_date, $end_date, $owner->getName(), null));
            $total_asmi = count($this->em->getRepository('OGIVEAlertBundle:ExpressionInterest')->getAllByQueriedParameters(null, $start_date, $end_date, $owner->getName(), null));
            $total_additifs = count($this->em->getRepository('OGIVEAlertBundle:Additive')->getAllByQueriedParameters(null, $start_date, $end_date, $owner->getName(), null));
            $total_attributions = count($this->em->getRepository('OGIVEAlertBundle:ProcedureResult')->getAllByQueriedParameters(null, $start_date, $end_date, $owner->getName(), null));
            $totaux = $total_aao + $total_additifs + $total_asmi + $total_attributions;
            $rows[] = array($owner->getName(), $total_aao . '', $total_asmi . '', $total_additifs . '', $total_attributions . '', $totaux . '');
        }
        //$writer->writeSheetRow($sheet1, array("Nombre de l'appels d'offres par M.O pendant la période allant du " . $start_date . " au " . $end_date));
        $writer->writeSheetHeader($sheet1, $header);
        foreach ($rows as $row) {
            $writer->writeSheetRow($sheet1, $row);
        }
        //$writer->markMergedCell($sheet1, $start_row = 0, $start_col = 0, $end_row = 0, $end_col = 4);
        $sheet2 = "Domaines";
        $header2 = array("Maître d'ouvrage" => "string", "AAO" => "string", "ASMI" => "string", "Additifs" => "string", "Attributions" => "string", "Toutes les offres" => "string");
        $rows2 = array();
        $domain = new \OGIVE\AlertBundle\Entity\Domain();
        foreach ($domains as $domain) {
            $total_aao = count($this->em->getRepository('OGIVEAlertBundle:CallOffer')->getAllByQueriedParameters(null, $start_date, $end_date, null, $domain->getId()));
            $total_asmi = count($this->em->getRepository('OGIVEAlertBundle:ExpressionInterest')->getAllByQueriedParameters(null, $start_date, $end_date, null, $domain->getId()));
            $total_additifs = count($this->em->getRepository('OGIVEAlertBundle:Additive')->getAllByQueriedParameters(null, $start_date, $end_date, null, $domain->getId()));
            $total_attributions = count($this->em->getRepository('OGIVEAlertBundle:ProcedureResult')->getAllByQueriedParameters(null, $start_date, $end_date, null, $domain->getId()));
            $totaux = $total_aao + $total_additifs + $total_asmi + $total_attributions;
            $rows2[] = array($domain->getName(), $total_aao . '', $total_asmi . '', $total_additifs . '', $total_attributions . '', $totaux.'');
        }
        $writer->writeSheetHeader($sheet2, $header2);
        foreach ($rows2 as $row) {
            $writer->writeSheetRow($sheet2, $row);
        }
        if (!is_dir($this->getExportExcelRootDir())) {
            mkdir($this->getExportExcelRootDir(), $mode = 0777, $recursive = true);
        }
        $writer->writeToFile($this->getExportExcelRootDir() . '/stats_appels_offres_infos.xlsx');
    }

    //Nomber of procedure by owner
    public function getStatisticsOfProceduresByMonth($start_date, $end_date) {
        set_include_path(get_include_path() . PATH_SEPARATOR . "..");
        include_once("xlsxwriter.class.php");
        $writer = new \XLSXWriter();
        $writer->setAuthor('SI OGIVE');
        $writer->setTempDir(sys_get_temp_dir()); //set custom tempdir
        $aaos = $this->em->getRepository('OGIVEAlertBundle:CallOffer')->getAllByQueriedParameters(null, $start_date, $end_date, null, null);
        $asmis = $this->em->getRepository('OGIVEAlertBundle:ExpressionInterest')->getAllByQueriedParameters(null, $start_date, $end_date, null, null);
        $additives = $this->em->getRepository('OGIVEAlertBundle:Additive')->getAllByQueriedParameters(null, $start_date, $end_date, null, null);
        $results = $this->em->getRepository('OGIVEAlertBundle:ProcedureResult')->getAllByQueriedParameters(null, $start_date, $end_date, null, null);
        $total_monthly_aaos = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $total_monthly_asmis = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $total_monthly_additives = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $total_monthly_results = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $header = array("Mois" => "string", "AAO" => "string", "ASMI" => "string", "Additifs" => "string", "Attributions" => "string", "Toutes les offres" => "string");
        $sheet1 = "Statistics By month";
        $rows = array();
        $procedure = new \OGIVE\AlertBundle\Entity\AlertProcedure();
        foreach ($aaos as $procedure) {
            $month_number = intval(date('n', strtotime($procedure->getPublicationDate())));
            $total_monthly_aaos[$month_number-1]+=1;
        }
        
        foreach ($asmis as $procedure) {
            $month_number = intval(date('n', strtotime($procedure->getPublicationDate())));
            $total_monthly_asmis[$month_number-1]+=1;
        }
        
        foreach ($additives as $procedure) {
            $month_number = intval(date('n', strtotime($procedure->getPublicationDate())));
            $total_monthly_additives[$month_number-1]+=1;
        }
        
        foreach ($results as $procedure) {
            $month_number = intval(date('n', strtotime($procedure->getPublicationDate())));
            $total_monthly_results[$month_number-1]+=1;
        }
        
        for ($i=0; $i<12; $i++) {
            $totaux = $total_monthly_aaos[$i] + $total_monthly_asmis[$i] + $total_monthly_additives[$i] + $total_monthly_results[$i];
            $rows[] = array($this->getMonthNameByNumber($i+1), $total_monthly_aaos[$i] . '', $total_monthly_asmis[$i] . '', $total_monthly_additives[$i] . '', $total_monthly_results[$i] . '', $totaux.'');
        }
        
        $writer->writeSheetHeader($sheet1, $header);
        foreach ($rows as $row) {
            $writer->writeSheetRow($sheet1, $row);
        }
        
        if (!is_dir($this->getExportExcelRootDir())) {
            mkdir($this->getExportExcelRootDir(), $mode = 0777, $recursive = true);
        }
        $writer->writeToFile($this->getExportExcelRootDir() . '/stats_appels_offres_infos.xlsx');
    }
    
    public function getMonthNameByNumber($monthNumber){
        switch ($monthNumber){
            case 1 :
                return "Janvier";
            case 2 :
                return "Février";
            case 3 :
                return "Mars";
            case 4 :
                return "Avril";
            case 5 :
                return "Mai";
            case 6 :
                return "Juin";
            case 7 :
                return "Juillet";
            case 8 :
                return "Août";
            case 9 :
                return "Septembre";
            case 10 :
                return "Octobre";
            case 11 :
                return "Novembre";
            case 12 :
                return "Décembre";
            default :
                return "";
        }
    }

    public function getExportExcelRootDir() {
        return __DIR__ . '/../../../../web/exports/excel';
    }

}
