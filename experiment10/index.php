<?php
session_start();
require_once 'db.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Initialize Focus Mode State
if (!isset($_SESSION['focus_mode'])) $_SESSION['focus_mode'] = false;

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_task'])) {
        $task_text = trim($_POST['task_text']);
        if (!empty($task_text)) {
            $stmt = $db->prepare("INSERT INTO tasks (user_id, task_text) VALUES (?, ?)");
            $stmt->execute([$user_id, $task_text]);
        }
    } elseif (isset($_POST['toggle_task'])) {
        $task_id = $_POST['task_id'];
        $stmt = $db->prepare("UPDATE tasks SET status = 1 - status WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
    } elseif (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
    } elseif (isset($_POST['start_focus'])) {
        $_SESSION['focus_mode'] = true;
    } elseif (isset($_POST['stop_focus'])) {
        $_SESSION['focus_mode'] = false;
    } elseif (isset($_POST['complete_next'])) {
        $task_id = $_POST['task_id'];
        // Mark current as done
        $stmt = $db->prepare("UPDATE tasks SET status = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        // Stay in focus mode to show next
    }
    header("Location: index.php");
    exit;
}

// Fetch All Tasks
$stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

// Analytics
$total_tasks = count($tasks);
$completed_tasks = count(array_filter($tasks, function($t) { return $t['status'] == 1; }));
$progress = ($total_tasks > 0) ? round(($completed_tasks / $total_tasks) * 100) : 0;

// Fetch Next Incomplete Task for Focus Mode
$stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = ? AND status = 0 ORDER BY created_at ASC LIMIT 1");
$stmt->execute([$user_id]);
$current_focus_task = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub Pro - Focus Mode</title>
    <style>
        :root { --primary: #8b5cf6; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); padding: 40px 20px; min-height: 100vh; }
        .container { max-width: 800px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .user-pill { background: var(--card); padding: 10px 20px; border-radius: 99px; display: flex; align-items: center; gap: 15px; border: 1px solid rgba(255,255,255,0.1); }
        .logout { color: #f87171; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--card); padding: 20px; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.05); }
        .stat-num { font-size: 2rem; font-weight: 800; color: var(--primary); display: block; }
        .stat-label { font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; }

        .progress-container { background: #334155; height: 12px; border-radius: 6px; overflow: hidden; margin-top: 10px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); }
        .progress-bar { background: linear-gradient(to right, #8b5cf6, #d8b4fe); height: 100%; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 10px rgba(139, 92, 246, 0.5); }

        .btn-primary { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.2s; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Focus Mode Styles */
        .focus-overlay { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 24px; padding: 60px 40px; text-align: center; border: 2px solid var(--primary); box-shadow: 0 20px 50px rgba(139, 92, 246, 0.2); }
        .focus-task-text { font-size: 2.5rem; font-weight: 800; margin-bottom: 40px; color: white; }
        .focus-actions { display: flex; justify-content: center; gap: 20px; }
        .btn-done { background: #10b981; font-size: 1.2rem; padding: 15px 40px; }
        .btn-stop { background: #475569; }

        /* Normal Mode Styles */
        .task-input-section { background: var(--card); padding: 25px; border-radius: 16px; margin-bottom: 30px; display: flex; gap: 15px; }
        input[type="text"] { flex: 1; background: #0f172a; border: 1px solid #334155; padding: 12px 20px; border-radius: 8px; color: white; font-size: 1rem; }
        .task-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .task-item { background: var(--card); padding: 15px 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; border: 1px solid rgba(255,255,255,0.03); transition: 0.2s; }
        .task-text { flex: 1; font-size: 1.05rem; }
        .task-item.completed .task-text { text-decoration: line-through; color: #64748b; }
        .btn-icon { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 1.2rem; }
        .empty-state { text-align: center; padding: 60px; color: #64748b; font-style: italic; }

        .confetti-text { font-size: 1.5rem; color: #4ade80; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div>
            <h1 style="font-size: 2rem; background: linear-gradient(to right, #a78bfa, #f472b6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">TaskHub Pro</h1>
            <p style="color: #94a3b8; font-size: 0.9rem;">Sequential Focus Workflow</p>
        </div>
        <div class="user-pill">
            <span><strong><?php echo $username; ?></strong></span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </header>

    <?php if ($_SESSION['focus_mode']): ?>
        <!-- FOCUS MODE UI -->
        <div class="focus-overlay">
            <?php if ($current_focus_task): ?>
                <p class="info-label" style="color: var(--accent); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 2px;">Current Focus</p>
                <div class="focus-task-text">"<?php echo htmlspecialchars($current_focus_task['task_text']); ?>"</div>
                
                <form method="POST" class="focus-actions">
                    <input type="hidden" name="task_id" value="<?php echo $current_focus_task['id']; ?>">
                    <button type="submit" name="complete_next" class="btn-primary btn-done">Done & Next →</button>
                    <button type="submit" name="stop_focus" class="btn-primary btn-stop">Exit Focus</button>
                </form>
            <?php else: ?>
                <div class="confetti-text">🎉 All Done for the Day!</div>
                <p style="margin-bottom: 30px; color: #94a3b8;">You've completed all your tasks. Great job!</p>
                <form method="POST">
                    <button type="submit" name="stop_focus" class="btn-primary">Return to Dashboard</button>
                </form>
            <?php endif; ?>

            <div style="margin-top: 50px; max-width: 400px; margin-left: auto; margin-right: auto;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #94a3b8;">
                    <span>Session Progress</span>
                    <span><?php echo $progress; ?>%</span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- NORMAL MODE UI -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-num"><?php echo $total_tasks; ?></span>
                <span class="stat-label">Total Tasks</span>
            </div>
            <div class="stat-card">
                <span class="stat-num"><?php echo $completed_tasks; ?></span>
                <span class="stat-label">Completed</span>
            </div>
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <span class="stat-label">Progress</span>
                    <span style="font-weight: bold; color: var(--primary);"><?php echo $progress; ?>%</span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem;">📋 Your Task List</h2>
            <?php if ($current_focus_task): ?>
                <form method="POST">
                    <button type="submit" name="start_focus" class="btn-primary" style="background: #10b981;">🚀 Start Focus Session</button>
                </form>
            <?php endif; ?>
        </div>

        <form class="task-input-section" method="POST">
            <input type="text" name="task_text" placeholder="Add a new task for today..." required autofocus>
            <button type="submit" name="add_task" class="btn-primary">Add Task</button>
        </form>

        <ul class="task-list">
            <?php if (empty($tasks)): ?>
                <div class="empty-state">No tasks found. Start by adding one above!</div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <li class="task-item <?php echo $task['status'] ? 'completed' : ''; ?>">
                        <form method="POST" style="display: flex; align-items: center; width: 100%; gap: 15px;">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit" name="toggle_task" class="btn-icon">
                                <?php echo $task['status'] ? '✅' : '⭕'; ?>
                            </button>
                            <span class="task-text"><?php echo htmlspecialchars($task['task_text']); ?></span>
                            <button type="submit" name="delete_task" class="btn-icon" onclick="return confirm('Delete this task?')">🗑️</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

    <a href="../index.html" style="display: block; text-align: center; margin-top: 50px; color: #64748b; text-decoration: none;">← Return to Laboratory Menu</a>
</div>

</body>
</html>
