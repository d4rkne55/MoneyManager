<?php

class MoneyManager extends Base
{
    /**
     * @Route '/'
     * @Route '/$id'
     * @Route '/$id/$dateRange'
     *
     * @param array $query
     */
    public function showTransfers(array $query) {
        if (isset($query['dateRange'])) {
            $date = $query['dateRange'];

            // when exact range, eg. '02.01.2017--09.01.17'
            if (preg_match('/^((\d{2,4})(\.(?2)){1,2})--(?1)$/', $date)) {
                $date = $this->handleDate(explode('--', $date), 'Y-m-d');
            }
            // when month given, eg. 'january-2017'
            elseif (preg_match('/^[a-z]+-\d{4}$/', $date)) {
                $date = str_replace('-', ' ', $date);
                $date = $this->handleDate(array($date, "last day of $date"), 'Y-m-d');
            }
            // when only year is given, eg. '2017'
            elseif (is_numeric($date) && strlen($date) == 4) {
                $date = array("$date-01-01", "$date-12-31");
            }
        }
        // if no date is set, show all until today
        else {
            $date = array(0, $this->handleDate('today', 'Y-m-d'));
        }

        $accountId = 0;
        $accounts = $this->DB->query('SELECT AccountID, AccountOwner FROM accounts')->fetchAll();
        $balance = 0;
        $transfers = array();

        if (isset($query['id']) && $query['id'] > 0) {
            $accountId = $query['id'];

            $stmt = $this->DB->prepare('SELECT * FROM transfers WHERE AccountID = ?  AND `Date` >= ? AND `Date` <= ? ORDER BY `Date` DESC, ID DESC');
            $stmt->execute(array($query['id'], $date[0], $date[1]));
            $transfers = $stmt->fetchAll();

            $balance = array_sum(array_column($transfers, 'Amount'));
            $balance = number_format($balance, 2, ',', '.');
        }

        $this->view->render('index.php', array(
            'id' => $accountId,
            'accounts' => $accounts,
            'balance' => $balance,
            'transfers' => $transfers,
            'newAcc' => count($transfers) == 0
        ));
    }

    /**
     * @Route '/add'
     *
     * @param array $data
     */
    public function addTransfer(array $data) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $aid = (int) $data['aid'];
            $date = $this->handleDate($data['date'], 'Y-m-d');
            $usage = trim($data['usage']);
            $amount = $this->moneyToNumber($data['amount']);

            $stmt = $this->DB->prepare('INSERT INTO transfers (AccountID, Amount, `Usage`, `Date`) VALUES (?, ?, ?, ?)');
            $stmt->execute(array($aid, $amount, $usage, $date));
        }
    }

    /**
     * Converts user input date(s) into legal format or, if no format specified, into a timestamp
     *
     * @param string|array $dates
     * @param string|bool  $format
     * @return mixed  depending on the parameters either a single date (string),
     *                a timestamp (int) or an (array) of dates
     */
    private function handleDate($dates, $format = false) {
        $modDates = array();
        $dates = (array) $dates;

        foreach ($dates as $date) {
            if ($date) {
                $date = explode('.', $date);
                if (empty($date[2]) && is_numeric($date[0])) {
                    $date[2] = date('Y');
                }
                else if (count($date) == 3 && preg_match('/^\d{2}$/', $date[2])) {
                    $date[2] = "20" . $date[2];
                }
                $date = implode('.', $date);
            } else {
                $date = "today";
            }
            $date = strtotime($date);
            if ($format) {
                $date = date($format, $date);
            }

            $modDates[] = $date;
        }

        return (count($modDates) > 1) ? $modDates : $modDates[0];
    }

    /**
     * Processes the transfer amount from user input to a number usable for PHP and the Database
     *
     * @param string $string
     * @return float
     */
    private function moneyToNumber($string) {
        // ability to calculate the sum for multiple things in one entry
        $string = explode('<br>', $string);

        $nums = array();
        foreach ($string as $num) {
            $num = str_replace(',', '.', $num);
            // remove currency symbols/names and thousands-separators
            $nums[] = preg_replace('/([^\d\.-]|\.(?=\d{3}))/', '', $num);
        }

        return (float) array_sum($nums);
    }
}