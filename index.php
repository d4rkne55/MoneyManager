<?php
require_once('dbconnect.php');

$id = (isset($_GET["id"])) ? $DB->real_escape_string($_GET["id"]) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MoneyManager - Financial Overview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Dennis Jungbauer">
    <meta name="editor" content="PhpStorm 9.0.2">
    <!-- Icon made by 'Freepik' from www.flaticon.com -->
    <link rel="shortcut icon" href="favicon.png">
    <link rel="stylesheet" href="normalize.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form name="account-selection">
    <label>Konto:</label>
    <select name="bank-account">
        <option value="std" <?php if ($id == 0) echo "selected"; ?>>-- Select a bank account --</option>
        <?php
        $sql1 = $DB->query("SELECT AccountID, AccountOwner FROM `money-manager_accounts`");
        while($account = $sql1->fetch_assoc()) {
            ?><option value="<?= $account["AccountID"] ?>" <?php if ($account["AccountID"] == $id) echo "selected"; ?>><?= $account["AccountID"] ?> - <?= $account["AccountOwner"] ?></option><?php
        }
        ?>
    </select>
</form>
<?php if ($id > 0) { ?>
<br>
<div class="centering-wrapper">
    <table class="bank-statement">
        <?php
        $sql2 = $DB->query("SELECT SUM(Amount) AS Balance FROM `money-manager_transfers` WHERE AccountID=$id");
        $balance = $sql2->fetch_assoc()["Balance"];
        ?>
        <caption>
            <span>Kontostand:&emsp;<?= $balance ?> &euro;</span>
        </caption>
        <tr>
            <th>Datum</th>
            <th>Verwendung</th>
            <th>Betrag</th>
        </tr>
        <tr contenteditable class="inactive">
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php
        $sql3 = $DB->query("SELECT * FROM `money-manager_transfers` WHERE AccountID=$id ORDER BY ID DESC");
        while($transfer = $sql3->fetch_assoc()) { ?>
            <tr>
                <td><?= date('d.m.Y', strtotime($transfer["Date"])); ?></td>
                <td><?= nl2br($transfer["Usage"]) ?></td>
                <td class="<?= ($transfer["Amount"] >= 0) ? "income" : "expense" ?>"><?= $transfer["Amount"] ?> &euro;</td>
            </tr>
        <?php } ?>
    </table>
    <div class="tooltip">
        <div class="arrow-shape"></div>
        <div class="tooltip-content">Klicke diese Zeile an um Überweisungen hinzuzufügen. Mit <kbd>Enter</kbd> speicherst du, mit <kbd>ESC</kbd> brichst du ab.</div>
    </div>
</div>
<?php } ?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>if(!window.jQuery) { document.write('<script src="jquery-2.2.4.min.js"><\/script>') }</script>
<script src="js.cookie.js"></script>
<script>
$(document).ready(function() {
    $('select[name="bank-account"]').change(function() {
        location.href = ($(this).val() == "std") ? location.pathname : "?id=" + $(this).val();
    });
    $('tr[contenteditable].inactive').mousedown(function(e) {
        if (e.button == 0) {
            $(this).removeClass("inactive");
            $('tr[contenteditable]').mouseleave().off("mouseenter mouseleave");
            if (typeof Cookies.get("tooltip") === "undefined") Cookies.set("tooltip", "false", {expires: 30});
        }
        else e.preventDefault();
    }).keydown(function(e) {
        if (e.keyCode == 27) {
            $(this).children("td").each(function() {
                $(this).html("");
            });
            $(this).addClass("inactive");
            $(this).blur();
        }
        if (e.keyCode == 13 && !e.shiftKey) {
            e.preventDefault();
            var data = $(this).children("td").toArray();
            $.post("add-transfer.php", {date: data[0].innerHTML, usage: data[1].innerHTML, amount: data[2].innerHTML, aid: 31290752}, function() {
                location.reload();
            });
        }
    });
    tooltip = $('.tooltip');
    var tooltipRead;
    $('tr[contenteditable]').hover(
        function() {
            if (typeof Cookies.get("tooltip") === "undefined") {
                var tooltipPosTop = $(this).position().top - 2;
                tooltip.stop().fadeIn(230).css({ display: "inline-block", top: tooltipPosTop+"px"});
                
                tooltipRead = setTimeout(function() {
                    Cookies.set("tooltip", "false", {expires: 30});
                    $(this).mouseleave().off("mouseenter mouseleave");
                }, 10000);
            }
        }, function() {
            tooltip.stop().fadeOut(400);
            clearTimeout(tooltipRead);
        }
    );
})
</script>
</body>
</html>