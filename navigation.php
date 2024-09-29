<!-- Main Navigation Start-->            
<nav id="nav">
    <div id="menu">
        <h3 class="menuarrow"><span>Menu</span></h3>
        <ul>
            <?php
            // Connect to server and select database.
            include("includes/config.php");
        
            // Creating query to fetch main menu from mysql database table.
            $main_menu_query = "SELECT * FROM main_menu";
            $main_menu_result = mysqli_query($conn, $main_menu_query);

            // Check if the query was successful
            if (!$main_menu_result) {
                die("Query failed: " . mysqli_error($conn));
            }

            while ($r = mysqli_fetch_array($main_menu_result)) {
            ?>
                <li>
                    <a href="<?php echo $r['mmenu_link']; ?>" id="<?php echo $r['mmenu_id']; ?>">
                        <?php echo $r['mmenu_name']; ?>
                    </a>
                    <div>
                        <ul>
            <?php
                // Creating query to fetch sub menu from mysql database table.
                $sub_menu_query = "SELECT * FROM sub_menu WHERE mmenu_id=" . $r['mmenu_id'];
                $sub_menu_result = mysqli_query($conn, $sub_menu_query);
                
                // Check if the query was successful
                if (!$sub_menu_result) {
                    die("Query failed: " . mysqli_error($conn));
                }

                while ($r1 = mysqli_fetch_array($sub_menu_result)) {
            ?>
                    <li>
                        <a href="<?php echo $r1['smenu_link']; ?>?Items=<?php echo $r1['0']; ?>&Subname=<?php echo $r1['2']; ?>&MenuCat=<?php echo $r1['1']; ?>">
                            <?php echo $r1['smenu_name']; ?>
                        </a>
                    </li>
            <?php 
                } 
            ?>
                        </ul>
                    </div>
                </li>
            <?php
            }
            ?>
        </ul>
    </div>
</nav>
<!-- Main Navigation End-->
