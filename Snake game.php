<?php
session_start();

// Load high score from the session or set it to zero if it's not set
$high_score = isset($_SESSION['high_score']) ? $_SESSION['high_score'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snake Game</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #2c3e50;
            font-family: Arial, sans-serif;
        }

        #game-container {
            text-align: center;
        }

        #gameCanvas {
            background-color: #34495e;
            display: block;
            margin: 0 auto;
        }

        #score-board {
            margin-top: 10px;
            color: #ecf0f1;
        }

        #score-board p {
            font-size: 18px;
        }

        /* Optional: Adding styles for the restart button if you want */
        #restartBtn {
            margin-top: 10px;
            padding: 10px;
            background-color: #16a085;
            color: white;
            border: none;
            cursor: pointer;
        }

        #restartBtn:hover {
            background-color: #1abc9c;
        }
    </style>
</head>
<body>

    <div id="game-container">
        <canvas id="gameCanvas" width="300" height="300"></canvas>
        <div id="score-board">
            <p>Score: <span id="score">0</span></p>
            <p>High Score: <span id="high-score"><?php echo $high_score; ?></span></p>
        </div>
        <!-- Optional restart button -->
        <button id="restartBtn" style="display:none;">Restart Game</button>
    </div>

    <script>
        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");
        const scoreElement = document.getElementById("score");
        const highScoreElement = document.getElementById("high-score");
        const restartBtn = document.getElementById("restartBtn");

        const gridSize = 10;
        let snake = [{ x: 5, y: 5 }];
        let direction = { x: 0, y: 0 };
        let food = { x: 0, y: 0 };
        let score = 0;
        let gameOver = false;

        // Set the initial position of the food
        function generateFood() {
            food.x = Math.floor(Math.random() * (canvas.width / gridSize)) * gridSize;
            food.y = Math.floor(Math.random() * (canvas.height / gridSize)) * gridSize;
        }

        // Draw everything on the canvas
        function drawGame() {
            if (gameOver) {
                alert("Game Over! Your score was " + score);
                resetGame();
                return;
            }

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw snake
            snake.forEach((segment, index) => {
                ctx.fillStyle = index === 0 ? "#2ecc71" : "#16a085";  // Head is green, body is teal
                ctx.fillRect(segment.x, segment.y, gridSize, gridSize);
            });

            // Draw food
            ctx.fillStyle = "#e74c3c"; // Red food
            ctx.fillRect(food.x, food.y, gridSize, gridSize);

            // Update score
            scoreElement.textContent = score;

            // Move the snake
            let head = { x: snake[0].x + direction.x, y: snake[0].y + direction.y };
            snake.unshift(head);

            // Check if the snake eats food
            if (head.x === food.x && head.y === food.y) {
                score++;
                generateFood();
            } else {
                snake.pop();
            }

            // Check for collisions
            if (head.x < 0 || head.x >= canvas.width || head.y < 0 || head.y >= canvas.height || collision(head)) {
                gameOver = true;
                saveHighScore();
                restartBtn.style.display = "block"; // Show restart button
            }
        }

        // Check if the snake collides with itself
        function collision(head) {
            for (let i = 1; i < snake.length; i++) {
                if (head.x === snake[i].x && head.y === snake[i].y) {
                    return true;
                }
            }
            return false;
        }

        // Save high score using PHP (AJAX request)
        function saveHighScore() {
            let currentHighScore = parseInt(highScoreElement.textContent);
            if (score > currentHighScore) {
                // Send new high score to the server
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "index.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("score=" + score);
            }
        }

        // Reset the game
        function resetGame() {
            score = 0;
            snake = [{ x: 5, y: 5 }];
            direction = { x: 0, y: 0 };
            gameOver = false;
            generateFood();
            drawGame();
            restartBtn.style.display = "none"; // Hide restart button
        }

        // Handle keyboard input for direction
        document.addEventListener("keydown", (e) => {
            if (e.key === "ArrowUp" && direction.y === 0) {
                direction = { x: 0, y: -gridSize };
            } else if (e.key === "ArrowDown" && direction.y === 0) {
                direction = { x: 0, y: gridSize };
            } else if (e.key === "ArrowLeft" && direction.x === 0) {
                direction = { x: -gridSize, y: 0 };
            } else if (e.key === "ArrowRight" && direction.x === 0) {
                direction = { x: gridSize, y: 0 };
            }
        });

        // Restart the game when button is clicked
        restartBtn.addEventListener("click", () => {
            resetGame();
        });

        // Game loop
        generateFood();
        setInterval(drawGame, 100);

        // Initialize the game
        resetGame();
    </script>

    <?php
    // Save high score if posted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score'])) {
        $score = intval($_POST['score']);
        $high_score = isset($_SESSION['high_score']) ? $_SESSION['high_score'] : 0;

        // Update high score if needed
        if ($score > $high_score) {
            $_SESSION['high_score'] = $score;
        }
    }
    ?>

</body>
</html>
