<?php
$students = [
    '2024001' => 'John Doe',
    '2024002' => 'Jane Smith',
    '2024003' => 'Peter Jones',
    // Add more student IDs and names here
];

session_start();
header('Content-Type: text/html');

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    switch ($action) {
        case 'getStudentName':
            $id = $_GET['id'] ?? '';
            $name = $students[$id] ?? '';
            echo json_encode(['name' => $name]);
            exit;
        case 'logTabLeave':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 'UnknownID';
            $name = $data['name'] ?? 'Anonymous';
            $date = date('Y-m-d H:i:s');
            $log_line = "Date: $date | ID: $id | Name: $name | Event: Tab minimized or left\n";
            file_put_contents('tab_leave_logs.txt', $log_line, FILE_APPEND | LOCK_EX);
            echo json_encode(['status' => 'logged']);
            exit;
        case 'saveEssayText':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 'UnknownID';
            $name = $data['name'] ?? 'Anonymous';
            $text = $data['text'] ?? '';
            $date = date('Y-m-d H:i:s');
            $content = "Date: $date\nName: $name\nID: $id\n\n$text\n";
            $file_path = "$id.txt";
            file_put_contents($file_path, $content, FILE_APPEND | LOCK_EX);
            echo json_encode(['status' => 'success']);
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Professor Dualos Academy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #c1d5e0);
            color: #333;
            margin: 0;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .container {
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        p {
            font-size: 1.1rem;
            line-height: 1.6;
        }
        button, input[type="text"] {
            width: 100%;
            padding: 0.9rem 1.2rem;
            margin: 0.6rem 0;
            font-size: 1rem;
            border: 2px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        input[type="text"] {
            border: 2px solid #ddd;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        textarea {
            width: 100%;
            font-size: 1.1rem;
            border-radius: 8px;
            box-sizing: border-box;
            resize: vertical;
            min-height: 180px;
            padding: 1rem;
            border: 2px solid #ddd;
        }
        .primary-btn {
            background: #3498db;
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        .primary-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
        @media (max-width: 600px) {
            h1 {
                font-size: 2rem;
            }
            .container {
                padding: 1.5rem;
                margin-top: 0;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <h1>📝 Student Write Box</h1>
    <div class="container" id="loginBox">
        <p>Please enter your Student ID number:</p>
        <input type="text" id="idInput" placeholder="Enter your Student ID" />
        <input type="text" id="nameInput" placeholder="Name will appear here" readonly style="display:none;" />
        <button id="loginBtn" class="primary-btn">🔓 Login</button>
    </div>
    <div class="container" id="essayBox" style="margin-top:2rem; display:none;">
        <h2>📝 Write here</h2>
        <textarea id="bigText" rows="10"></textarea>
        <button id="saveTextBtn" class="primary-btn">💾 Save Text</button>
        <div id="saveTextResult" style="margin-top:1rem; text-align:center;"></div>
    </div>
    <script>
        let studentName = "";
        let studentId = "";

        const loginBtn = document.getElementById('loginBtn');
        const idInput = document.getElementById('idInput');
        const nameInput = document.getElementById('nameInput');
        const loginBox = document.getElementById('loginBox');
        const essayBox = document.getElementById('essayBox');
        const bigText = document.getElementById('bigText');
        const saveTextBtn = document.getElementById('saveTextBtn');
        const saveTextResult = document.getElementById('saveTextResult');

        // Auto-display name as you type the ID
        idInput.addEventListener('input', async () => {
            const id = idInput.value.trim();
            if (id !== "") {
                const res = await fetch(`?action=getStudentName&id=${encodeURIComponent(id)}`);
                const data = await res.json();
                if (data.name) {
                    nameInput.value = data.name;
                    nameInput.style.display = 'block';
                } else {
                    nameInput.value = "ID not found!";
                    nameInput.style.display = 'block';
                }
            } else {
                nameInput.style.display = 'none';
            }
        });

        // Allow Enter key to login
        idInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                loginBtn.click();
            }
        });

        loginBtn.addEventListener('click', () => {
            const id = idInput.value.trim();
            const name = nameInput.value.trim();
            if (id === "") {
                alert("Please enter your Student ID number.");
                return;
            }
            if (name === "" || name === "ID not found!") {
                alert("Please enter a valid Student ID.");
                return;
            }
            studentId = id;
            studentName = name;
            loginBox.style.display = 'none';
            essayBox.style.display = 'block';
        });

        // Save text to server as [ID].txt
        saveTextBtn.addEventListener('click', async () => {
            const text = bigText.value.trim();
            if (!text) {
                saveTextResult.textContent = "Please write something before saving.";
                return;
            }
            const res = await fetch(`?action=saveEssayText`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: studentId, name: studentName, text })
            });
            const data = await res.json();
            if (data.status === 'success') {
                saveTextResult.textContent = "✅ Saved successfully!";
                bigText.value = "";
            } else {
                saveTextResult.textContent = "❌ Error saving text.";
            }
        });

        // Log tab leave event
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden' && studentId && studentName) {
                fetch('?action=logTabLeave', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: studentId,
                        name: studentName
                    })
                });
            }
        });
    </script>
</body>
</html>
