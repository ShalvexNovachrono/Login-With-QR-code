<?php
    session_start(); 
    extract($_REQUEST);

    $db = new PDO('sqlite: db.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $res = $db->exec(
        "CREATE TABLE IF NOT EXISTS `qrcode` (
        code TEXT NOT NULL,
        approved TEXT NOT NULL
        )"
    );

    function getRandomWord($len) {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }


    if (!isset($_GET["qrmade"])) {
        $code = getRandomWord(10);
        $approved = "False";
        $stmt = $db->prepare(
            "INSERT INTO qrcode (code, approved) VALUES (:code, :approved)");
        
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':approved', $approved, PDO::PARAM_STR);
        
        $stmt->execute();
        header("Location: ?qrmade={$code}");
    } elseif (isset($_GET["checkqrcode"])) {
        $result = $db->query("SELECT * FROM qrcode");
        $count = 0;
        
        foreach ($result as $row) { 
            if ($row['code'] === $_GET["checkqrcode"] && $row['approved'] === "True") {  
                $count = 1;
                break;
            }
        }

        if ($count === 1) {
            $stmt = $db->prepare("DELETE FROM qrcode WHERE code = :code");
            $stmt->bindParam(':code', $_GET["checkqrcode"], PDO::PARAM_STR);
            $stmt->execute();
            echo "approved";
        } else {
            echo "not approved";
        }
    } elseif (isset($_GET["approved"])) {
        
    } elseif (isset($_GET["done"])) {
        echo "<script type='text/javascript'>\nwindow.close();\n</script>";
    } elseif (isset($_GET["scanmade"])) {
        
        $result = $db->query("SELECT * FROM qrcode");
        $count = 0;
        
        foreach ($result as $row) { 
            if ($row['code'] === $_GET["scanmade"]) {  
                $count = 1;
                break;
            }
        }

        if ($count === 1) {
            $approved = "True";
            $stmt = $db->prepare("Update qrcode SET approved = :approved WHERE code = :code");
        
            $stmt->bindParam(':code', $_GET["scanmade"], PDO::PARAM_STR);
            $stmt->bindParam(':approved', $approved, PDO::PARAM_STR);
            
            $stmt->execute();
            header("Location: ?done&qrmade");
        }
    }
    if (!isset($_GET["checkqrcode"])) {

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR CODE</title>
</head>
<body>

<?php 
    if (isset($_GET["approved"])) {
        echo "<h1>approved, welcome in comrade</h1>";
    } 


    if (isset($_GET["qrmade"])) {
        if (!isset($_GET["approved"])) {
    ?>
    <h1>Scan this qr code to login/goin</h1>
    <div id="qrCode"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        var qr = new QRCode(document.getElementById("qrCode"), "https://qr-code-to-login.nava10y.repl.co/?qrmade=true&scanmade=<?php echo $_GET["qrmade"]; ?>");

        setInterval(function() {
            $.ajax({
                    url:'https://qr-code-to-login.nava10y.repl.co/',
                    data: {checkqrcode: "<?php echo $_GET["qrmade"];?>", qrmade: true},
                    method: 'get',
                    success: function(data) {
                        if (data === "approved") {
                            window.location.href = "https://qr-code-to-login.nava10y.repl.co/?qrmade=true&approved";
                        } else {
                            console.log(data);
                        }
                    }
            })
        }, 1500);
    </script>
<?php 
        }

    } ?>
</body>
</html>
<?php
    }
?>