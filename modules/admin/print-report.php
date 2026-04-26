<?php
session_start();
include_once '../../config/database.php';

if (!isset($_SESSION['user_role'])) { header("Location: ../auth/login.php"); exit; }

$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

function fetchReportData($conn, $date, $type) {
    $data = [];
    $res = mysqli_query($conn, "SELECT * FROM hospital_accounts WHERE date = '$date' AND type = '$type' ORDER BY category ASC");
    while($row = mysqli_fetch_assoc($res)) {
        $data[$row['category']][] = $row;
    }
    return $data;
}

$income_groups = fetchReportData($conn, $filter_date, 'income');
$expense_groups = fetchReportData($conn, $filter_date, 'expense');

$grand_total_income = 0;
foreach($income_groups as $cat) foreach($cat as $r) $grand_total_income += $r['amount'];
$grand_total_expense = 0;
foreach($expense_groups as $cat) foreach($cat as $r) $grand_total_expense += $r['amount'];
$balance = $grand_total_income - $grand_total_expense;
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Professional Report - <?php echo $filter_date; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Inter:wght@400;600;700&display=swap');
        
        body { font-family: 'Inter', 'Hind Siliguri', sans-serif; background: #f0f0f0; margin: 0; padding: 0; color: #1a202c; }
        
        .report-paper {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        /* হেডার ডিজাইন */
        .header { text-align: center; border-bottom: 4px double #0A2647; padding-bottom: 10px; margin-bottom: 25px; }
        .header h1 { margin: 0; color: #0A2647; font-size: 30px; font-weight: 800; text-transform: uppercase; }
        .header p { margin: 2px 0; font-weight: 600; color: #4a5568; font-size: 14px; }
        .report-badge { 
            background: #0A2647; color: white; display: inline-block; 
            padding: 4px 25px; border-radius: 50px; font-size: 12px; 
            margin-top: 10px; text-transform: uppercase; font-weight: 700;
        }

        .meta-row { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 12px; border-bottom: 1px solid #edf2f7; padding-bottom: 8px; }

        /* সামারি গ্রিড */
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px; }
        .sum-card { border: 1px solid #e2e8f0; padding: 15px; text-align: center; border-radius: 12px; }
        .sum-card small { text-transform: uppercase; font-size: 10px; color: #718096; font-weight: 700; display: block; margin-bottom: 5px; }
        .sum-card strong { font-size: 18px; color: #1a202c; }
        .in-bg { background: #f0fff4; border-color: #c6f6d5; }
        .ex-bg { background: #fff5f5; border-color: #fed7d7; }
        .ba-bg { background: #ebf8ff; border-color: #bee3f8; }

        /* টেবিল কন্টেইনার অ্যাডজাস্টমেন্ট */
        .details-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start; }
        .section-title { font-size: 13px; font-weight: 800; text-transform: uppercase; color: #2d3748; margin-bottom: 10px; border-left: 4px solid #0A2647; padding-left: 10px; }
        
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px; }
        th { background: #f7fafc; padding: 8px; border: 1px solid #edf2f7; text-align: left; color: #4a5568; }
        td { padding: 7px 10px; border: 1px solid #edf2f7; vertical-align: top; }
        
        .cat-head { background: #edf2f7; font-weight: 700; color: #2d3748; }
        .sub-total-row { background: #f8fafc; font-weight: 800; border-top: 1.5px solid #cbd5e0; }

        /* সিগনেচার সেকশন */
        .sig-footer { margin-top: 60px; display: flex; justify-content: space-between; }
        .sig-line { border-top: 1.5px solid #2d3748; width: 180px; text-align: center; padding-top: 5px; font-size: 12px; font-weight: 700; color: #2d3748; }

        /* প্রিন্ট বাটন */
        .print-btn { position: fixed; top: 20px; right: 20px; background: #0A2647; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; z-index: 999; }

        @media print {
            .print-btn { display: none; }
            body { background: white; }
            .report-paper { margin: 0; box-shadow: none; width: 100%; padding: 10mm; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Download Report</button>

    <div class="report-paper">
        <div class="header">
            <h1>Patient Care Hospital</h1>
            <p>Hospital & Diagnostic Center</p>
            <p>College Road, Barguna | Phone: 01331-434347</p>
            <div class="report-badge">Daily Accounts Statement</div>
        </div>

        <div class="meta-row">
            <span><strong>Date:</strong> <?php echo date('d F, Y', strtotime($filter_date)); ?></span>
            <span><strong>Time:</strong> <?php echo date('h:i A'); ?></span>
        </div>

        <div class="summary-grid">
            <div class="sum-card in-bg">
                <small>Total Income</small>
                <strong>৳ <?php echo number_format($grand_total_income, 2); ?></strong>
            </div>
            <div class="sum-card ex-bg">
                <small>Total Expense</small>
                <strong>৳ <?php echo number_format($grand_total_expense, 2); ?></strong>
            </div>
            <div class="sum-card ba-bg">
                <small>Closing Balance</small>
                <strong>৳ <?php echo number_format($balance, 2); ?></strong>
            </div>
        </div>

        <div class="details-wrapper">
            <!-- ইনকাম সেকশন -->
            <div>
                <div class="section-title">Income Details</div>
                <table>
                    <thead>
                        <tr><th>Description</th><th style="text-align:right">Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php if(empty($income_groups)) echo "<tr><td colspan='2' style='text-align:center'>No Records</td></tr>"; ?>
                        <?php foreach($income_groups as $catName => $rows): 
                            $catSub = 0; ?>
                            <tr class="cat-head"><td colspan="2"><?php echo $catName; ?></td></tr>
                            <?php foreach($rows as $row): $catSub += $row['amount']; ?>
                                <tr>
                                    <td><small style="color:#718096">#<?php echo $row['receipt_no']; ?></small><br><?php echo $row['description']; ?></td>
                                    <td style="text-align:right"><?php echo number_format($row['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="sub-total-row">
                                <td style="text-align:right">Sub Total:</td>
                                <td style="text-align:right">৳ <?php echo number_format($catSub, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- এক্সপেন্স সেকশন -->
            <div>
                <div class="section-title">Expense Details</div>
                <table>
                    <thead>
                        <tr><th>Description</th><th style="text-align:right">Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php if(empty($expense_groups)) echo "<tr><td colspan='2' style='text-align:center'>No Records</td></tr>"; ?>
                        <?php foreach($expense_groups as $catName => $rows): 
                            $catSub = 0; ?>
                            <tr class="cat-head"><td colspan="2"><?php echo $catName; ?></td></tr>
                            <?php foreach($rows as $row): $catSub += $row['amount']; ?>
                                <tr>
                                    <td><small style="color:#718096">#<?php echo $row['receipt_no']; ?></small><br><?php echo $row['description']; ?></td>
                                    <td style="text-align:right"><?php echo number_format($row['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="sub-total-row">
                                <td style="text-align:right">Sub Total:</td>
                                <td style="text-align:right">৳ <?php echo number_format($catSub, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: right; border-bottom: 2px solid #0A2647; padding-bottom: 5px;">
            <span style="font-size: 14px; font-weight: 700;">Net Balance in Cash: ৳ <?php echo number_format($balance, 2); ?></span>
        </div>

        <div class="sig-footer">
            <div class="sig-line">Prepared By (Accounts)</div>
            <div class="sig-line">Managing Director</div>
        </div>

        <div style="margin-top: 50px; text-align: center; font-size: 9px; color: #a0aec0;">
            This statement is computer generated and part of Patient Care Hospital Management System.
        </div>
    </div>

</body>
</html>