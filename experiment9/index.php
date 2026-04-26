<?php
session_start();

$history_file = 'history.txt';

// Initialize session variables
if (!isset($_SESSION['display'])) $_SESSION['display'] = '0';
if (!isset($_SESSION['last_val'])) $_SESSION['last_val'] = '';
if (!isset($_SESSION['operator'])) $_SESSION['operator'] = '';
if (!isset($_SESSION['clear_screen'])) $_SESSION['clear_screen'] = false;

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $btn = $_POST['btn'] ?? '';
    
    if (is_numeric($btn) || $btn == '.') {
        if ($_SESSION['display'] == '0' || $_SESSION['clear_screen']) {
            $_SESSION['display'] = $btn;
            $_SESSION['clear_screen'] = false;
        } else {
            $_SESSION['display'] .= $btn;
        }
    } elseif ($btn == 'C') {
        $_SESSION['display'] = '0';
        $_SESSION['last_val'] = '';
        $_SESSION['operator'] = '';
    } elseif ($btn == 'CE') {
        $_SESSION['display'] = '0';
    } elseif ($btn == 'back') {
        $_SESSION['display'] = substr($_SESSION['display'], 0, -1);
        if ($_SESSION['display'] == '') $_SESSION['display'] = '0';
    } elseif ($btn == '+/-') {
        if ($_SESSION['display'] != '0') {
            if (strpos($_SESSION['display'], '-') === 0) {
                $_SESSION['display'] = substr($_SESSION['display'], 1);
            } else {
                $_SESSION['display'] = '-' . $_SESSION['display'];
            }
        }
    } elseif (in_array($btn, ['+', '-', '*', '/'])) {
        $_SESSION['last_val'] = $_SESSION['display'];
        $_SESSION['operator'] = $btn;
        $_SESSION['clear_screen'] = true;
    } elseif ($btn == '=') {
        if ($_SESSION['operator'] != '' && $_SESSION['last_val'] != '') {
            $n1 = (float)$_SESSION['last_val'];
            $n2 = (float)$_SESSION['display'];
            $res = 0;
            $op = $_SESSION['operator'];
            
            switch ($op) {
                case '+': $res = $n1 + $n2; break;
                case '-': $res = $n1 - $n2; break;
                case '*': $res = $n1 * $n2; break;
                case '/': $res = ($n2 != 0) ? $n1 / $n2 : 'Error'; break;
            }
            
            $log = "$n1 $op $n2 = $res" . PHP_EOL;
            $current_history = file_exists($history_file) ? file_get_contents($history_file) : "";
            file_put_contents($history_file, $log . $current_history);
            
            $_SESSION['display'] = (string)$res;
            $_SESSION['operator'] = '';
            $_SESSION['clear_screen'] = true;
        }
    } elseif ($btn == '1/x') {
        $n = (float)$_SESSION['display'];
        $_SESSION['display'] = ($n != 0) ? (string)(1 / $n) : 'Error';
    } elseif ($btn == 'x2') {
        $n = (float)$_SESSION['display'];
        $_SESSION['display'] = (string)($n * $n);
    } elseif ($btn == 'sqrt') {
        $n = (float)$_SESSION['display'];
        $_SESSION['display'] = ($n >= 0) ? (string)sqrt($n) : 'Error';
    } elseif ($btn == 'clear_hist') {
        file_put_contents($history_file, "");
    }
}

// Read History
$history = file_exists($history_file) ? file($history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$history = array_slice($history, 0, 15);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proper Calculator - Experiment 9</title>
    <style>
        :root {
            --bg-color: #202020;
            --calc-bg: #2b2b2b;
            --btn-digit: #3b3b3b;
            --btn-op: #323232;
            --btn-equal: #76b9ed;
            --text-color: #ffffff;
            --border-radius: 6px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: #1a1a1a;
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .main-layout {
            display: flex;
            gap: 40px;
            max-width: 900px;
            width: 100%;
        }

        .calculator {
            background-color: var(--calc-bg);
            width: 340px;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            font-size: 0.9rem;
            color: #ccc;
        }

        .display {
            padding: 30px 15px;
            text-align: right;
            font-size: 3rem;
            font-weight: 600;
            min-height: 120px;
            word-wrap: break-word;
            word-break: break-all;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }

        .btn {
            border: none;
            height: 50px;
            border-radius: var(--border-radius);
            font-size: 1rem;
            color: white;
            background: var(--btn-op);
            cursor: pointer;
            transition: background 0.1s;
        }

        .btn:hover {
            background: #404040;
        }

        .btn-digit {
            background: var(--btn-digit);
            font-weight: 600;
        }

        .btn-digit:hover {
            background: #4a4a4a;
        }

        .btn-equal {
            background: var(--btn-equal);
            color: #000;
        }

        .btn-equal:hover {
            background: #a5d3f5;
        }

        .history-panel {
            flex: 1;
            background: #252525;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .history-panel h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #888;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .history-item {
            padding: 12px 0;
            border-bottom: 1px solid #333;
            font-size: 0.95rem;
            color: #bbb;
        }

        .btn-clear-hist {
            background: transparent;
            border: 1px solid #444;
            color: #888;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .btn-clear-hist:hover {
            background: #333;
            color: #fff;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .back-link:hover {
            color: #aaa;
        }
    </style>
</head>
<body>

<div class="main-layout">
    <div class="calculator">
        <div class="header">
            <div>☰ Standard</div>
            <div>⏱</div>
        </div>

        <div class="display">
            <?php echo $_SESSION['display']; ?>
        </div>

        <form method="POST" class="grid">
            <!-- Memory Buttons Placeholder -->
            <button class="btn" style="background:transparent; color:#888; font-size:0.8rem;">MC</button>
            <button class="btn" style="background:transparent; color:#888; font-size:0.8rem;">MR</button>
            <button class="btn" style="background:transparent; color:#888; font-size:0.8rem;">M+</button>
            <button class="btn" style="background:transparent; color:#888; font-size:0.8rem;">M-</button>

            <!-- Row 1 -->
            <button name="btn" value="%" class="btn">%</button>
            <button name="btn" value="CE" class="btn">CE</button>
            <button name="btn" value="C" class="btn">C</button>
            <button name="btn" value="back" class="btn">⌫</button>

            <!-- Row 2 -->
            <button name="btn" value="1/x" class="btn">¹/x</button>
            <button name="btn" value="x2" class="btn">x²</button>
            <button name="btn" value="sqrt" class="btn">²√x</button>
            <button name="btn" value="/" class="btn">÷</button>

            <!-- Digits Row 1 -->
            <button name="btn" value="7" class="btn btn-digit">7</button>
            <button name="btn" value="8" class="btn btn-digit">8</button>
            <button name="btn" value="9" class="btn btn-digit">9</button>
            <button name="btn" value="*" class="btn">×</button>

            <!-- Digits Row 2 -->
            <button name="btn" value="4" class="btn btn-digit">4</button>
            <button name="btn" value="5" class="btn btn-digit">5</button>
            <button name="btn" value="6" class="btn btn-digit">6</button>
            <button name="btn" value="-" class="btn">-</button>

            <!-- Digits Row 3 -->
            <button name="btn" value="1" class="btn btn-digit">1</button>
            <button name="btn" value="2" class="btn btn-digit">2</button>
            <button name="btn" value="3" class="btn btn-digit">3</button>
            <button name="btn" value="+" class="btn">+</button>

            <!-- Row 4 -->
            <button name="btn" value="+/-" class="btn btn-digit">+/-</button>
            <button name="btn" value="0" class="btn btn-digit">0</button>
            <button name="btn" value="." class="btn btn-digit">.</button>
            <button name="btn" value="=" class="btn btn-equal">=</button>
        </form>

        <a href="../index.html" class="back-link">← Back to Experiments</a>
    </div>

    <div class="history-panel">
        <h2>
            History
            <form method="POST" style="display:inline;">
                <button type="submit" name="btn" value="clear_hist" class="btn-clear-hist">Clear</button>
            </form>
        </h2>
        
        <?php if (empty($history)): ?>
            <p style="color: #555; text-align:center; margin-top:50px;">There's no history yet</p>
        <?php else: ?>
            <?php foreach ($history as $item): ?>
                <div class="history-item"><?php echo htmlspecialchars($item); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
