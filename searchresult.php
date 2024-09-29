<?php
session_start();

// User is already logged in. Redirect them somewhere useful.
if (isset($_SESSION['username'])) {
    $User = $_SESSION['username'];
} else {
    $User = "";
}
?>

<!-- Head1 Part Start-->
<?php include("head1.html"); ?>
<!-- Head1 Part End-->

<!-- Top Part Start-->
<?php 
if ($User != "") {
    include("top_links2.php");
} else {
    include("top_links.php");
}
?>
<!-- Top Part End-->

<!-- Main Div Tag Start-->
<div id="wrapper">

    <!-- Header Part Start-->
    <?php 
    if ($User != "") {
        include("header2.php");
    } else {
        include("header.php");
    }
    ?>
    <!-- Header Part End-->
    
    <!-- Middle Part Start--> 
    <!-- Section Start--> 
    <?php include("section.html"); ?>
    <!--Section End-->
    <!--Middle Part End-->

    <!--Search Result Start-->
    <div class="box mb0" id="search">
        <div class="box-heading-1"><span>Search Result</span></div>
        <div class="box-content-1">
            <div class="box-product-1">
                <?php
                include("includes/config.php");

                $search = $_POST['search'];
                $select = $_POST['select'];
                
                switch($select) {
                    case 'name':
                        $sql = "SELECT * FROM `jewellery` WHERE (CONVERT(`prodname` USING utf8) LIKE '%$search%')";
                        break;
                    case 'desc':
                        $sql = "SELECT * FROM `jewellery` WHERE (CONVERT(`descr` USING utf8) LIKE '%$search%')";
                        break;
                    case 'price':
                        $sql = "SELECT * FROM `jewellery` WHERE `price` = $search";
                        break;
                    case 'views':
                        $sql = "SELECT * FROM `jewellery` WHERE `noviews` = $search";
                        break;
                    case 'type':
                        $sql = "SELECT * FROM `jewellery` WHERE (CONVERT(`type` USING utf8) LIKE '%$search%')";
                        break;
                }
                
                $min_length = 1; // minimum length of the search
                if (strlen($search) >= $min_length) {
                    $search = htmlspecialchars($search); // sanitize input
                    $search = mysqli_real_escape_string($conn, $search); // use mysqli function

                    $raw_results = mysqli_query($conn, $sql) or die(mysqli_error($conn));

                    if (mysqli_num_rows($raw_results) > 0) {
                        $count = 0;

                        while ($results = mysqli_fetch_array($raw_results)) {
                            $id = $results["id"];
                            $prodname = $results["prodname"];
                            $path = $results["path"];
                            $category = $results["category"];
                            $price = "Rs. " . $results["price"];
                            $desc = $results["descr"];
                            $type = $results["type"];
                            $views = $results["noviews"];
                            $width = "150px";
                            $height = "150px";
                            
                            $list = '';
                            $src = "Photos/";

                            $list .= '
                                <div>
                                    <div class="image"><a href="' . $src . $path . '"><img width="' . $width . '" height="' . $height . '" src="' . $src . $path . '" alt="' . $prodname . '"></a></div>
                                    <div class="proName">
                                        <div class="name"><a href="' . $src . $path . '">' . $desc . '</a></div>
                                        <div class="price">' . $price . '</div>
                                        <div class="cart" align="center">
                                            <label class="btn">';

                            if (isset($_SESSION['username'])) {
                                $list .= '<form method="post" action="view.php"><input type="hidden" name="txtid" value="' . $id . '"><input type="submit" value="Add to Cart" class="button"/></form>';
                            }
                            
                            $list .= '
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            '; // end list here
                            
                            $count += 1;
                            echo $list;
                        }
                        
                        if ($count > 1) {
                            echo "<script>alert('Search Found: " . $count . " Results')</script>";
                        } else {
                            echo "<script>alert('Search Found: " . $count . " Result')</script>";
                        }
                    } else {
                        echo "<b>No results</b>";
                    }
                } else {
                    echo "<b>Minimum length is </b>" . $min_length;
                }
                ?>
            </div>
        </div>
    </div>
    <!--Search Result End-->
    
    <!--Special Promo Banner Start-->
    <div class="box-promo" id="box-promo">
        <div class="box-heading-1"><span>PROMO ON FEATURED ITEMS</span></div>
        <div id="banner0" class="banner">
            <div style="display:block;"><img src="image/addBanner-940x145.jpg" alt="Special Offers" title="Special Offers" /></div>
        </div>
    </div>
    <!--Special Promo Banner End--> 

    <!--Footer Part Start-->
    <?php include("footer.php"); ?>
    <!--Footer Part End-->
</div>
<!-- Main Div Tag End-->

<!--Flexslider Javascript Part Start-->
<?php include("flexslider.php"); ?>
<!--Flexslider Javascript Part End-->
</body>
</html>
