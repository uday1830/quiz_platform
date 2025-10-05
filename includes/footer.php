<?php
if (!isset($baseUrl)) {
    $baseUrl = '/' . explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0];
}
?>
    <script src="<?php echo $baseUrl; ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/custom.js"></script>
</body>
</html>
