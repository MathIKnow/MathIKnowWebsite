<script src="//code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<script src="/assets/js/moment-timezone-with-data.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.js" crossorigin="anonymous"></script>
<?php
foreach($scripts as $script) {
    ?>
<script src="<?=$script?>"></script>
    <?php
}
?>
<script type="text/javascript" defer src="https://donorbox.org/install-popup-button.js"></script>
<script>window.DonorBox = { widgetLinkClassName: 'custom-dbox-popup' }</script>
<footer>
    <div class="container">
        <!--<a class="custom-dbox-popup" href="https://donorbox.org/mathiknow-site-funding">
            <img src="https://d1iczxrky3cnb2.cloudfront.net/button-medium-blue.png"  alt="Donate Button"/>
        </a>-->
    </div>
</footer>
<?php
?>