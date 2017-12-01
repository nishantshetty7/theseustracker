<?php
session_start();
    require_once 'includes/functions.php';

    if(!isset($_SESSION['user']))
    {
        redirect_to("index.php");
    }
    require 'includes/connection.php';

    $user = $_SESSION['user'];
    $username = $user['username'];

	$todaydate = date("Y-m-d");

    if(isset($_POST['branddata'])){
        $branddata = $_POST['branddata'];
        $brandname = $branddata['brandname'];
        $since = $branddata['since'];
        $until = $branddata['until'];
        $_SESSION['brandname'] = $brandname;
        $_SESSION['since'] = $since;
        $_SESSION['until'] = $until;
        $handleids = $branddata['brandhandles'];
        $handleids = implode(",", $handleids);
        $handlequery = "select id,name,uniqueid,platform_id from tracker_handle where id in ($handleids)";
        $result = mysql_query($handlequery);
        $brandhandles = array();
        $platform_handles = array();
        while($row = mysql_fetch_array($result)) {
            $brandhandles[$row['id']] = $row;
            if(!isset($platform_handles[$row['platform_id']])){
                $platform_handles[$row['platform_id']] = array();
            }
            array_push($platform_handles[$row['platform_id']], $row);
        }
        $_SESSION['platform_handles'] = $platform_handles;
    }
    else{
        if(isset($_SESSION['platform_handles'])){
            $since = $_SESSION['since'];
            $until = $_SESSION['until'];
            $brandname = $_SESSION['brandname'];
            $platform_handles = $_SESSION['platform_handles'];
            $sincestr = date_format(date_create($since),"dMY");
            $untilstr = date_format(date_create($until),"dMY");
        }
        else{
            $platform_handles = array();
        }

        $out = fopen('php://output', 'w');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=BRAND_'.str_replace(" ","_",$brandname).'_'.$sincestr.'_'.$untilstr.'.csv');

        // Facebook Handles
        if(isset($platform_handles[1])){
            foreach($platform_handles[1] as $facebookhandle){
                $facebook_handle = $facebookhandle['id'];
                $facebookquery = "select * from tracker_socialmediafacebook where handle_id = '$facebook_handle' and reportdate between '$since' and '$until' order by reportdate desc";
                $result = mysql_query($facebookquery);
                $pagerecords = mysql_numrows($result);
                if($pagerecords > 0)
                {
                    fputcsv($out, array("Facebook ".$facebookhandle['name']));
                    fputcsv($out, explode(",","Date,Page Likes,New Page Likes,Post Likes,Brand Posts,Comments,Shares"));
                    $fbmetrics = array("reportdate","pagelikes","newpagelikes","postlikes","brandposts","comments","shares");
                    while($row = mysql_fetch_array($result)) {
                        $fbrow = array();
                        foreach($fbmetrics as $metric){
                            if($metric == "reportdate"){
                                $row[$metric] = date_format(date_create($row[$metric]),"d-M-Y");
                            }
                            array_push($fbrow,$row[$metric]);
                        }
                        fputcsv($out, $fbrow);
                    }
                }
                else{
                    fputcsv($out, array("No Facebook Numbers For ".$facebookhandle['name']));
                    fputcsv($out, array(""));
                }
            }
        }
        else{
            fputcsv($out, array("No Facebook Handles For ".$brandname));
        }

        fputcsv($out, array(""));
        fputcsv($out, array(""));

        // Twitter Handles
        if(isset($platform_handles[2])){
            foreach($platform_handles[2] as $twitterhandle){
                $twitter_handle = $twitterhandle['id'];
                $twitterquery = "select * from tracker_socialmediatwitter where handle_id = '$twitter_handle' and reportdate between '$since' and '$until' order by reportdate desc";
                $result = mysql_query($twitterquery);
                $pagerecords = mysql_numrows($result);
                if($pagerecords > 0)
                {
                    fputcsv($out, array("Twitter ".$twitterhandle['name']));
                    fputcsv($out, explode(",","Date,Followers,New Followers,Tweets,Retweets,Favorites"));
                    $twmetrics = array("reportdate","followers","newfollowers","tweets","retweets","favorites");
                    while($row = mysql_fetch_array($result)) {
                        $twrow = array();
                        foreach($twmetrics as $metric){
                            if($metric == "reportdate"){
                                $row[$metric] = date_format(date_create($row[$metric]),"d-M-Y");
                            }
                            array_push($twrow,$row[$metric]);
                        }
                        fputcsv($out, $twrow);
                    }
                }
                else{
                    fputcsv($out, array("No Twitter Numbers For ".$twitterhandle['name']));
                    fputcsv($out, array(""));
                }
            }
        }
        else{
            fputcsv($out, array("No Twitter Handles For ".$brandname));
        }

        fputcsv($out, array(""));
        fputcsv($out, array(""));

        // Youtube Handles
        if(isset($platform_handles[3])){
            foreach($platform_handles[3] as $youtubehandle){
                $youtube_handle = $youtubehandle['id'];
                $youtubequery = "select * from tracker_socialmediayoutube where handle_id = '$youtube_handle' and reportdate between '$since' and '$until' order by reportdate desc";
                $result = mysql_query($youtubequery);
                $pagerecords = mysql_numrows($result);
                if($pagerecords > 0)
                {
                    fputcsv($out, array("Youtube ".$youtubehandle['name']));
                    fputcsv($out, explode(",","Date,All Time Views,New Views,Subscribers,New Subscribers,Videos,Likes,Dislikes,Comments"));
                    $ytmetrics = array("reportdate","alltimeviews","newviews","subscribers",
                                        "newsubscribers","videos","likes","dislikes","comments");
                    while($row = mysql_fetch_array($result)) {
                        $ytrow = array();
                        foreach($ytmetrics as $metric){
                            if($metric == "reportdate"){
                                $row[$metric] = date_format(date_create($row[$metric]),"d-M-Y");
                            }
                            array_push($ytrow,$row[$metric]);
                        }
                        fputcsv($out, $ytrow);
                    } 
                }
                else{
                    fputcsv($out, array("No Youtube Numbers For ".$youtubehandle['name']));
                    fputcsv($out, array(""));
                }
            }
        }
        else{
            fputcsv($out, array("No Youtube Handles For ".$brandname));
        }

        fputcsv($out, array(""));
        fputcsv($out, array(""));

        // Instagram Handles
        if(isset($platform_handles[4])){
            foreach($platform_handles[4] as $instagramhandle){
                $instagram_handle = $instagramhandle['id'];
                $instagramquery = "select * from tracker_socialmediainstagram where handle_id = '$instagram_handle' and reportdate between '$since' and '$until' order by reportdate desc";
                $result = mysql_query($instagramquery);
                $pagerecords = mysql_numrows($result);
                if($pagerecords > 0)
                {
                    fputcsv($out, array("Instagram ".$instagramhandle['name']));
                    fputcsv($out, explode(",","Date,Followers,New Followers,Posts,Likes,Comments,Views"));
                    $igmetrics = array("reportdate","followers","newfollowers","posts",
                                        "likes","comments","views");
                    while($row = mysql_fetch_array($result)) {
                        $igrow = array();
                        foreach($igmetrics as $metric){
                            if($metric == "reportdate"){
                                $row[$metric] = date_format(date_create($row[$metric]),"d-M-Y");
                            }
                            array_push($igrow,$row[$metric]);
                        }
                        fputcsv($out, $igrow);
                    }
                }
                else{
                    fputcsv($out, array("No Instagram Numbers For ".$instagramhandle['name']));
                    fputcsv($out, array(""));
                }
            }
        }
        else{
            fputcsv($out, array("No Instagram Handles For ".$brandname));
        }

        fclose($out);
    }
?>