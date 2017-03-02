<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>MoneyManager - Financial Overview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Dennis Jungbauer">
    <meta name="editor" content="PhpStorm for Mac 2016.2.2">
    <!-- Icon made by 'Vectorgraphit' from www.flaticon.com is licensed by CC 3.0 BY -->
    <!-- http://www.flaticon.com/free-icon/euro-symbol_15507 -->
    <link rel="shortcut icon" href="<?= ROOT ?>img/favicon.png">
    <link rel="stylesheet" href="<?= ROOT ?>css/normalize.css">
    <link rel="stylesheet" href="<?= ROOT ?>css/style.css">
</head>
<body>
<form name="account-selection">
    <label>Konto:</label>
    <select name="bank-account">
        <option value="std">-- Select a bank account --</option>
        <?php
        foreach ($this->accounts as $account) {
            $selected = ($this->id == $account['AccountID']) ? 'selected' : '';
            $optionText = sprintf('%08d', $account['AccountID']) . " - $account[AccountOwner]";
            ?>
            <option value="<?= $account['AccountID'] ?>" <?= $selected ?>><?= $optionText ?></option>
            <?php
        }
        ?>
    </select>
</form>
<?php if ($this->id > 0) { ?>
<br>
<div class="centering-wrapper">
    <table class="bank-statement <?= $this->newAcc ? 'new-account' : '' ?>">
        <caption>
            <span>Kontostand:&emsp;<?= $this->balance ?> €</span>
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
        foreach ($this->transfers as $transfer) {
            $transferDate = date('d.m.Y', strtotime($transfer['Date']));
            $transferType = ($transfer['Amount'] >= 0) ? 'income' : 'expense';
            $transferAmount = str_replace('.', ',', $transfer['Amount']);
            ?>
            <tr>
                <td><?= $transferDate ?></td>
                <td><?= $transfer['Usage'] ?></td>
                <td class="<?= $transferType ?>"><?= $transferAmount ?> €</td>
            </tr>
            <?php
        }
        ?>
    </table>

    <div class="tooltip">
        <div class="arrow-shape"></div>
        <div class="tooltip-content">Klicke diese Zeile an um Überweisungen hinzuzufügen. Mit <kbd>Enter</kbd> speicherst du, mit <kbd>ESC</kbd> brichst du ab.</div>
    </div>
</div>
<?php } ?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>if(!window.jQuery) { document.write('<script src="<?= ROOT ?>js/jquery-2.2.4.min.js"><\/script>') }</script>
<script src="<?= ROOT ?>js/js.cookie.js"></script>
<script>
    $(document).ready(function() {
        $('select[name="bank-account"]').change(function() {
            location.href = ($(this).val() == "std") ? "<?= ROOT ?>" : "<?= ROOT ?>" + $(this).val();
        });

        setTimeout(function() {
            if ($('.bank-statement').hasClass('new-account')) {
                $('tr[contenteditable].inactive').trigger('mousedown', "triggered");
            }
        }, 300);

        $('tr[contenteditable].inactive').mousedown(function(e, triggered) {
            if (e.button == 0 || triggered) {
                $(this).removeClass('inactive');
                $('tr[contenteditable]').mouseleave().off('mouseenter mouseleave');
                if (typeof Cookies.get('tooltip') === "undefined") Cookies.set('tooltip', "false", {expires: 30});
            }
            else e.preventDefault();
        }).keydown(function(e) {
            if (e.keyCode == 27) {
                $(this).children('td').each(function() {
                    $(this).html("");
                });
                $(this).addClass('inactive');
                $(this).blur();
            }
            if (e.keyCode == 13 && !e.shiftKey) {
                e.preventDefault();

                var data = $(this).children('td').toArray();
                $.post('<?= ROOT ?>add', { aid: <?= ($this->id) ? $this->id : 0 ?>, date: data[0].innerHTML, usage: data[1].innerHTML, amount: data[2].innerHTML }, function() {
                    location.reload();
                });
            }
        });

        tooltip = $('.tooltip');
        var tooltipRead;
        $('tr[contenteditable]').hover(
            function() {
                if (typeof Cookies.get('tooltip') === "undefined") {
                    var tooltipPosTop = $(this).position().top - 2;
                    tooltip.stop().fadeIn(230).css({ display: "inline-block", top: tooltipPosTop+"px"});

                    tooltipRead = setTimeout(function() {
                        Cookies.set('tooltip', "false", {expires: 30});
                        $(this).mouseleave().off('mouseenter mouseleave');
                    }, 10000);
                }
            }, function() {
                tooltip.stop().fadeOut(400);
                clearTimeout(tooltipRead);
            }
        );
    });
</script>
</body>
</html>