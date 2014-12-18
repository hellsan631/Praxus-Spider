<?php

include_once "./header.php";

global $core;

if($_SERVER['REQUEST_METHOD'] == 'POST'){

	$submitType = intval($_POST['submitType']);

    //new member
	if($submitType == 0){

        foreach($_POST as $key => $value){

            if($value == "Link 1")
                $_POST[$key] = 0;
            else if($value == "Link 2")
                $_POST[$key] = 0;
            else if($value == "Link 3")
                $_POST[$key] = 0;

        }

        /*
         * id;
         * name;
         * title;
         * join_date;
         * parent_id;
         * rank_sort;
         * link1;
         * link2;
         * link3;
         * image_url;
         */

        $member = new member();

        $member->setValue('name', $_POST['name']);
        $member->setValue('title', $_POST['title']);
        $member->setValue('join_date', $_POST['join_date']);
        $member->setValue('parent_id', $_POST['parent_id']);
        $member->setValue('rank_sort', $_POST['rank_sort']);
        $member->setValue('link1', $_POST['link1']);
        $member->setValue('link2', $_POST['link2']);
        $member->setValue('link3', $_POST['link3']);
        $member->setValue('color', $_POST['color']);

        if(!empty($_FILES["image"]) && ($_FILES['image']['error'] == 0)){//File Upload Code

            $path = realpath("./");
            $path = str_replace("\\", "/", $path);
            $uploaddir = "/storage/profiles/";
            $uploaded = false;
            $file = $uploaddir . basename($_FILES['image']['name']);
            $ext = explode('.',$file);
            $ext = $ext[1];
            $short = md5($file.strtotime("now"));
            $short = substr($short, 0, 16);

            if (!file_exists('../'.$uploaddir)) {
                mkdir('../'.$uploaddir, 0777, true);
            }

            $file = "{$path}/../{$uploaddir}{$short}.{$ext}";

            if (move_uploaded_file($_FILES['image']['tmp_name'], $file)) {
                $uploaded = ".{$uploaddir}{$short}.{$ext}";
            }

        }

        $member->setValue('image_url', $uploaded);

        if($member->saveNew()){
            $_SESSION['result'] = "Successfully Added New Member";
        }else if(!isset($_SESSION['result'])){
            $_SESSION['result'] = "Unable To Add New Member";
        }

        header("Location: ./admin.php");

	}
    //save member
    else if($submitType == 1){
        foreach($_POST as $key => $value){

            if($value == "Link 1")
                $_POST[$key] = 0;
            else if($value == "Link 2")
                $_POST[$key] = 0;
            else if($value == "Link 3")
                $_POST[$key] = 0;

        }

        $member = new member();

        $member->setValue('id', $_POST['id']);

        $member->setValue('name', $_POST['name']);
        $member->setValue('title', $_POST['title']);
        $member->setValue('join_date', $_POST['join_date']);
        $member->setValue('parent_id', $_POST['parent_id']);
        $member->setValue('rank_sort', $_POST['rank_sort']);
        $member->setValue('link1', $_POST['link1']);
        $member->setValue('link2', $_POST['link2']);
        $member->setValue('link3', $_POST['link3']);
        $member->setValue('color', $_POST['color']);

        if(!empty($_FILES["image"]) && ($_FILES['image']['error'] == 0)){//File Upload Code

            $path = realpath("./");
            $path = str_replace("\\", "/", $path);
            $uploaddir = "/storage/profiles/";
            $uploaded = false;
            $file = $uploaddir . basename($_FILES['image']['name']);
            $ext = explode('.',$file);
            $ext = $ext[1];
            $short = md5($file.strtotime("now"));
            $short = substr($short, 0, 16);

            if (!file_exists('../'.$uploaddir)) {
                mkdir('../'.$uploaddir, 0777, true);
            }

            $file = "{$path}/../{$uploaddir}{$short}.{$ext}";

            if (move_uploaded_file($_FILES['image']['tmp_name'], $file)) {
                $uploaded = ".{$uploaddir}{$short}.{$ext}";
            }else{
                $uploaded = $_POST['image_url'];
            }

        }else{
            $uploaded = $_POST['image_url'];
        }

        $member->setValue('image_url', $uploaded);

        if($member->saveOld()){
            $_SESSION['result'] = "Successfully Saved Existing Member";
        }else if(!isset($_SESSION['result'])){
            $_SESSION['result'] = "Unable To Saved Existing Member";
        }

        header("Location: ./admin.php");
    }

}
