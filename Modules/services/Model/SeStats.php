<?php

require_once 'Framework/Model.php';
require_once 'Modules/services/Model/SeProject.php';

require_once 'Modules/core/Model/CoreTranslator.php';
// require_once 'externals/PHPExcel/Classes/PHPExcel.php';

/**
 * Class defining the supplies pricing model
 *
 * @author Sylvain Prigent
 */
class SeStats extends Model {

    public function computeStatsProjects($startDate_min, $startDate_max) {

        // total number of projects 
        $sql = "select * from se_project where date_open >= ? AND date_open <= ?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();

        // number of accademic and industry projects
        $numberAccademicProjects = 0;
        $numberIndustryProjects = 0;

        //$modelUser = new CoreUser();
        $modelClient = new ClClient();
        $modelPricing = new ClPricing();

        foreach ($projects as $project) {

            // get the responsible unit
            $clientInfo = $modelClient->get($project["id_resp"]);
            $pricingInfo = $modelPricing->get($clientInfo["pricing"]);
            if ($pricingInfo["type"] == 1) {

                $numberAccademicProjects++;
            } else {
                $numberIndustryProjects++;
            }
        }

        // number of new academic projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_project=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 2));
        $numberNewAccademicProject = $req->rowCount();

        //echo "numberNewAccademicProject = " . $numberNewAccademicProject . "<br/>";
        // number of new academic team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 2));
        $numberNewAccademicTeam = $req->rowCount();

        //echo "numberNewAccademicTeam = " . $numberNewAccademicTeam . "<br/>";
        // number of new industry projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_project=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 3));
        $numberNewIndustryProject = $req->rowCount();

        //echo "numberNewIndustryProject = " . $numberNewIndustryProject . "<br/>";
        // number of new industry team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 3));
        $numberNewIndustryTeam = $req->rowCount();

        //echo "numberNewIndustryTeam = " . $numberNewIndustryTeam . "<br/>";

        $purcentageNewIndustryTeam = 0;
        $purcentageloyaltyIndustryProjects = 0;
        if ($numberIndustryProjects > 0) {
            $purcentageNewIndustryTeam = round(100 * $numberNewIndustryTeam / $numberIndustryProjects);
            $purcentageloyaltyIndustryProjects = round(100 * ($numberIndustryProjects - $numberNewIndustryTeam) / $numberIndustryProjects);
        }

        $purcentageNewAccademicTeam = 0;
        $purcentageloyaltyAccademicProjects = 0;
        if ($numberAccademicProjects > 0) {
            $purcentageNewAccademicTeam = round(100 * $numberNewAccademicTeam / $numberAccademicProjects);
            $purcentageloyaltyAccademicProjects = round(100 * ($numberAccademicProjects - $numberNewAccademicTeam) / $numberAccademicProjects);
        }

        $output = array("numberNewIndustryTeam" => $numberNewIndustryTeam,
            "purcentageNewIndustryTeam" => $purcentageNewIndustryTeam,
            "numberIndustryProjects" => $numberIndustryProjects,
            "loyaltyIndustryProjects" => $numberIndustryProjects - $numberNewIndustryTeam,
            "purcentageloyaltyIndustryProjects" => $purcentageloyaltyIndustryProjects,
            "numberNewAccademicTeam" => $numberNewAccademicTeam,
            "purcentageNewAccademicTeam" => $purcentageNewAccademicTeam,
            "numberAccademicProjects" => $numberAccademicProjects,
            "loyaltyAccademicProjects" => $numberAccademicProjects - $numberNewAccademicTeam,
            "purcentageloyaltyAccademicProjects" => $purcentageloyaltyAccademicProjects,
            "totalNumberOfProjects" => $totalNumberOfProjects
        );
        return $output;
    }
    
    public function computeDelayStats($id_space, $periodStart, $periodEnd){
        // total number of projects 
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND id_space=?";
        $req = $this->runRequest($sql, array($periodStart, $periodEnd, $id_space));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();
        
        if (count($projects) == 0){
            return array(
                "numberIndustryProjectInDelay" => 0,
                      "percentageIndustryProjectInDelay" => 0,  
                      "numberIndustryProjectOutDelay" => 0,
                      "percentageIndustryProjectOutDelay" => 0,
                      "numberAcademicProjectInDelay" => 0,
                      "percentageAcademicProjectInDelay" => 0,  
                      "numberAcademicProjectOutDelay" => 0,
                      "percentageAcademicProjectOutDelay" => 0,  
            
            );
        }

        // number of accademic and industry projects
        $numberIndustryProjectInDelay = 0;
        $numberIndustryProjectOutDelay = 0;
        $numberAcademicProjectInDelay = 0;
        $numberAcademicProjectOutDelay = 0;
        
        $modelClient = new ClClient();
        $modelPricing = new ClPricing();

        foreach ($projects as $project) {
            
            // get the responsible unit
            $clientInfo = $modelClient->get($project["id_resp"]);
            $pricingInfo = $modelPricing->get($clientInfo["pricing"]);
            
            $onTime = true;
            if ($project["date_close"] != "" && $project["date_close"] != "0000-00-00"
                && $project["time_limit"] != "" && $project["time_limit"] != "0000-00-00"    ){
                if ( $project["date_close"] > $project["time_limit"]){
                    $onTime = false;
                }
            }
            
            /*
            $onTime = false;
            
            if( $project["date_close"] == "0000-00-00" ){
                $project["date_close"] = date("Y-m-d", time());
            }
            if( $project["time_limit"] == "0000-00-00" || $project["time_limit"] >= $project["date_close"]  ){
                $onTime = true;
            }
            */
            
            
            if ($pricingInfo["type"] == 1) {
                
                if($onTime){
                    $numberIndustryProjectInDelay++;
                }
                else{
                    $numberIndustryProjectOutDelay++;
                }
                
            } else {
                
                if($onTime){
                    $numberAcademicProjectInDelay++;
                }
                else{
                    $numberAcademicProjectOutDelay++;
                }
            }
            
        }
        
        return array( "numberIndustryProjectInDelay" => $numberIndustryProjectInDelay,
                      "percentageIndustryProjectInDelay" => 100*($numberIndustryProjectInDelay / $totalNumberOfProjects),  
                      "numberIndustryProjectOutDelay" => $numberIndustryProjectOutDelay,
                      "percentageIndustryProjectOutDelay" => 100*($numberIndustryProjectOutDelay / $totalNumberOfProjects),
                      "numberAcademicProjectInDelay" => $numberAcademicProjectInDelay,
                      "percentageAcademicProjectInDelay" => 100*($numberAcademicProjectInDelay / $totalNumberOfProjects),  
                      "numberAcademicProjectOutDelay" => $numberAcademicProjectOutDelay,
                      "percentageAcademicProjectOutDelay" => 100*($numberAcademicProjectOutDelay / $totalNumberOfProjects),  
            );
    }

    public function getResponsiblesCsv($startDate_min, $startDate_max, $lang) {
        $sql = "select distinct id_resp from se_project where date_open >= ? AND date_open <= ?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();


        $modelUser = new CoreUser();


        $content = CoreTranslator::Name($lang) . ";" . CoreTranslator::Email($lang) . "\r\n";
        foreach ($projects as $project) {
            $userName = $modelUser->getUserFUllName($project["id_resp"]);
            $userMail = $modelUser->getUserEmail($project["id_resp"]);
            $content .= $userName . ";" . $userMail . "\r\n";
        }

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=listing_responsible_sproject.csv");
        echo $content;
    }

    public function computeOriginStats($id_space, $periodStart, $periodEnd){
    
        $academique = $this->computeSingleOriginStats($id_space, $periodStart, $periodEnd, 2);
        $private = $this->computeSingleOriginStats($id_space, $periodStart, $periodEnd, 3);
        
        return array("academique" => $academique, "private" => $private);
    }
    
    public function computeSingleOriginStats($id_space, $periodStart, $periodEnd, $academic_private){
        
        $stats = array();
        
        $sql = "SELECT * FROM se_origin WHERE id_space = ? ORDER BY display_order ASC;";
        $origins = $this->runRequest($sql, array($id_space))->fetchAll();
        
        foreach ($origins as $origin){
            $sql = "SELECT * FROM se_project WHERE date_open >= ? AND date_open <= ? AND id_space=? AND new_project=? AND id_origin=?";
            $req = $this->runRequest($sql, array($periodStart, $periodEnd, $id_space, $academic_private, $origin["id"]));
            
            $stats[] = array("id_origin" => $origin["id"], "origin" => $origin["name"], "count" => $req->rowCount());
        }
        return $stats;
        
    }
    
    public function computeStats($id_space, $startDate_min, $startDate_max) {

        // total number of projects 
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND id_space=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, $id_space));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();

        // number of accademic and industry projects
        $numberAccademicProjects = 0;
        $numberIndustryProjects = 0;

        //$modelUser = new CoreUser();
        $modelClient = new ClClient();
        $modelPricing = new ClPricing();

        foreach ($projects as $project) {

            // get the responsible unit
            $clientInfo = $modelClient->get($project["id_resp"]);

            $pricingInfo = $modelPricing->get($clientInfo["pricing"]);
            
            if ($pricingInfo["type"] == 1) {

                $numberAccademicProjects++;
            } else {
                $numberIndustryProjects++;
            }
        }

        // number of new academic projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_project=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 2));
        $numberNewAccademicProject = $req->rowCount();

        //echo "numberNewAccademicProject = " . $numberNewAccademicProject . "<br/>";
        // number of new academic team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 2));
        $numberNewAccademicTeam = $req->rowCount();

        //echo "numberNewAccademicTeam = " . $numberNewAccademicTeam . "<br/>";
        // number of new industry projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_project=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 3));
        $numberNewIndustryProject = $req->rowCount();

        //echo "numberNewIndustryProject = " . $numberNewIndustryProject . "<br/>";
        // number of new industry team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 3));
        $numberNewIndustryTeam = $req->rowCount();

        //echo "numberNewIndustryTeam = " . $numberNewIndustryTeam . "<br/>";

        $purcentageNewIndustryTeam = 0;
        $purcentageloyaltyIndustryProjects = 0;
        if ($numberIndustryProjects > 0) {
            $purcentageNewIndustryTeam = round(100 * $numberNewIndustryTeam / $numberIndustryProjects);
            $purcentageloyaltyIndustryProjects = round(100 * ($numberIndustryProjects - $numberNewIndustryTeam) / $numberIndustryProjects);
        }

        $purcentageNewAccademicTeam = 0;
        $purcentageloyaltyAccademicProjects = 0;
        if ($numberAccademicProjects > 0) {
            $purcentageNewAccademicTeam = round(100 * $numberNewAccademicTeam / $numberAccademicProjects);
            $purcentageloyaltyAccademicProjects = round(100 * ($numberAccademicProjects - $numberNewAccademicTeam) / $numberAccademicProjects);
        }

        $output = array("numberNewIndustryTeam" => $numberNewIndustryTeam,
            "purcentageNewIndustryTeam" => $purcentageNewIndustryTeam,
            "numberIndustryProjects" => $numberIndustryProjects,
            "loyaltyIndustryProjects" => $numberIndustryProjects - $numberNewIndustryTeam,
            "purcentageloyaltyIndustryProjects" => $purcentageloyaltyIndustryProjects,
            "numberNewAccademicTeam" => $numberNewAccademicTeam,
            "purcentageNewAccademicTeam" => $purcentageNewAccademicTeam,
            "numberAccademicProjects" => $numberAccademicProjects,
            "loyaltyAccademicProjects" => $numberAccademicProjects - $numberNewAccademicTeam,
            "purcentageloyaltyAccademicProjects" => $purcentageloyaltyAccademicProjects,
            "totalNumberOfProjects" => $totalNumberOfProjects
        );
        return $output;
    }

}
