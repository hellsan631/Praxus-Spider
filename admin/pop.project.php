
<?php

include_once "./header.php";

$member_id = (int)$_GET['id'];

$member = new Member();
$member = $member->loadInstance($member_id);

$members = new member;
$members = $members->getList();

?>


<div id="background" class="clear-fix">
    <div id="overlay" style="width:600px;">

        <?php

            if($member === false):

        ?>

        <form action="admin-listener.php" enctype="multipart/form-data" method="post">
            <input type="hidden" name="submitType" value="0" />
            <h5>Profile Image:</h5><input name="image" type="file" />
            <br/><br/>
            <input type="text" id="name" name="name" value="Name" /><br/>
            <input type="text" id="title" name="title" value="Title" /><br/>
            <input type="text" id="join_date" name="join_date" value="Join Date" /><br/>
            <input type="text" id="color" name="color" value="Ribbon Color" /><br/>
            <select name="parent_id" id="parent_id">
                <option value="0" selected>Select A Parent</option>
                <?php

                foreach($members as $temp){

                    echo "<option value='{$temp->getValue('id')}'>{$temp->getValue('name')}</option>";

                }

                ?>
            </select><br/>
            <input type="text" id="rank_sort" name="rank_sort" value="Rank Sort" /><br/>
            <input type="text" id="link1" name="link1" value="Link 1" /><br/>
            <input type="text" id="link2" name="link2" value="Link 2" /><br/>
            <input type="text" id="link3" name="link3" value="Link 3" /><br/>
            <input class="button" type="submit" value="submit" />
        </form>

        <?php

            else:

        ?>

        <form action="admin-listener.php" enctype="multipart/form-data" method="post">
            <input type="hidden" name="submitType" value="1" />
            <input type="hidden" name="id" value="<?php echo $member->getValue('id');?>" />
            <input type="hidden" name="image_url" value="<?php echo $member->getValue('image_url');?>" />
            <h4>Header Image:</h4><input name="image" type="file" /><br/>
            <?php

                if($member->getValue('image_url') !== 0 || $member->getValue('image_url') !== false):

            ?>
            <h5><?php echo $member->getValue('image_url');?></h5>
            <img src="../<?php echo $member->getValue('image_url');?>" style="width:200px; max-height:200; height:auto;" />
            <?php endif; ?>
            <br/><br/>
            <input type="text" id="name" name="name" value="<?php if($member->getValue('name') != null){echo $member->getValue('name').'" class="non';}else echo 'Name'; ?>" /><br/>
            <input type="text" id="title" name="title" value="<?php if($member->getValue('title') != null){echo $member->getValue('title').'" class="non';}else echo 'Title'; ?>" /><br/>
            <input type="text" id="join_date" name="join_date" value="<?php if($member->getValue('join_date') != null){echo $member->getValue('join_date').'" class="non';}else echo 'Join Date'; ?>" /><br/>
            <input type="text" id="color" name="color" value="<?php if($member->getValue('color') != null){echo $member->getValue('color').'" class="non';}else echo 'Ribbon Color'; ?>" /><br/>
            <select name="parent_id" id="parent_id">
                <option value="0">Select A Parent</option>
                <?php

                foreach($members as $temp){

                    if($temp->id == $member->getValue('parent_id')){
                        echo "<option value='{$temp->getValue('id')}' selected>{$temp->getValue('name')}</option>";
                    }else{
                        echo "<option value='{$temp->getValue('id')}'>{$temp->getValue('name')}</option>";
                    }

                }

                ?>
            </select><br/>
            <input type="text" id="rank_sort" name="rank_sort" value="<?php if($member->getValue('rank_sort') != null){echo $member->getValue('rank_sort').'" class="non';}else echo 'Rank Sort'; ?>" /><br/>
            <input type="text" id="link1" name="link1" value="<?php if($member->getValue('link1') != null){echo $member->getValue('link1').'" class="non';}else echo 'Link 1'; ?>" /><br/>
            <input type="text" id="link2" name="link2" value="<?php if($member->getValue('link2') != null){echo $member->getValue('link2').'" class="non';}else echo 'Link 2'; ?>" /><br/>
            <input type="text" id="link3" name="link3" value="<?php if($member->getValue('link3') != null){echo $member->getValue('link3').'" class="non';}else echo 'Link 3'; ?>" /><br/>
            <input class="button" type="submit" value="submit" />
        </form>

        <?php

            endif;

        ?>
    </div>
</div>