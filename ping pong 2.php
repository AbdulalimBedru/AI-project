<?php
// PHP code to handle high score saving
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player1Score = $_POST['player1Score'];
    $player2Score = $_POST['player2Score'];
    $highscoreFile = "highscores.txt";

    // Read current high scores
    $highscores = file_exists($highscoreFile) ? file($highscoreFile, FILE_IGNORE_NEW_LINES) : ["0", "0"];

    // Update the high score file
    if ($player1Score > $player2Score) {
        $highscores[0] = max($highscores[0], $player1Score);
    } else {
        $highscores[1] = max($highscores[1], $player2Score);
    }

    // Save updated high scores
    file_put_contents($highscoreFile, implode("\n", $highscores));

    // Return the high scores as JSON
    echo json_encode($highscores);
    exit;
}

// Read high scores
$highscoreFile = "highscores.txt";
$highscores = file_exists($highscoreFile) ? file($highscoreFile, FILE_IGNORE_NEW_LINES) : ["0", "0"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ping Pong Game</title>
    <style>
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #333;
            font-family: Arial, sans-serif;
        }
        #game-container {
            text-align: center;
        }
        #ping-pong-game {
            border: 2px solid white;
            background-color: black;
        }
        #score {
            color: white;
            font-size: 24px;
            margin-top: 20px;
        }
        #start-button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        #start-button:hover {
            background-color: #45a049;
        }
        #highscore {
            color: white;
            font-size: 18px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div id="game-container">
        <canvas id="ping-pong-game" width="600" height="400"></canvas>
        <div id="score">
            <span id="player1-score">0</span> : <span id="player2-score">0</span>
        </div>
        <div id="highscore">
            Highscore - Player 1: <span id="highscore-player1"><?= $highscores[0] ?></span> | Player 2: <span id="highscore-player2"><?= $highscores[1] ?></span>
        </div>
        <button id="start-button">Start New Game</button>
    </div>

    <script>
        const canvas = document.getElementById("ping-pong-game");
        const ctx = canvas.getContext("2d");

        const paddleWidth = 10;
        const paddleHeight = 100;
        const ballSize = 10;

        let player1Y = canvas.height / 2 - paddleHeight / 2;
        let player2Y = canvas.height / 2 - paddleHeight / 2;
        let ballX = canvas.width / 2;
        let ballY = canvas.height / 2;
        let ballSpeedX = 5;
        let ballSpeedY = 3;

        let player1Score = 0;
        let player2Score = 0;

        let player1Up = false;
        let player1Down = false;
        let player2Up = false;
        let player2Down = false;

        function drawGame() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = "white";
            ctx.fillRect(0, player1Y, paddleWidth, paddleHeight);
            ctx.fillRect(canvas.width - paddleWidth, player2Y, paddleWidth, paddleHeight);

            ctx.beginPath();
            ctx.arc(ballX, ballY, ballSize, 0, Math.PI * 2);
            ctx.fillStyle = "white";
            ctx.fill();
            ctx.closePath();

            ballX += ballSpeedX;
            ballY += ballSpeedY;

            if (ballY <= 0 || ballY >= canvas.height) {
                ballSpeedY = -ballSpeedY;
            }

            if (ballX <= paddleWidth) {
                if (ballY > player1Y && ballY < player1Y + paddleHeight) {
                    ballSpeedX = -ballSpeedX;
                } else {
                    player2Score++;
                    resetBall();
                }
            }

            if (ballX >= canvas.width - paddleWidth - ballSize) {
                if (ballY > player2Y && ballY < player2Y + paddleHeight) {
                    ballSpeedX = -ballSpeedX;
                } else {
                    player1Score++;
                    resetBall();
                }
            }

            document.getElementById("player1-score").textContent = player1Score;
            document.getElementById("player2-score").textContent = player2Score;
        }

        function resetBall() {
            ballX = canvas.width / 2;
            ballY = canvas.height / 2;
            ballSpeedX = -ballSpeedX;
            ballSpeedY = 3 * (Math.random() > 0.5 ? 1 : -1);
        }

        function movePaddles() {
            if (player1Up && player1Y > 0) player1Y -= 10;
            if (player1Down && player1Y < canvas.height - paddleHeight) player1Y += 10;
            if (player2Up && player2Y > 0) player2Y -= 10;
            if (player2Down && player2Y < canvas.height - paddleHeight) player2Y += 10;
        }

        function gameLoop() {
            drawGame();
            movePaddles();
            requestAnimationFrame(gameLoop);
        }

        document.addEventListener("keydown", (e) => {
            if (e.key === "w") player1Up = true;
            if (e.key === "s") player1Down = true;
            if (e.key === "ArrowUp") player2Up = true;
            if (e.key === "ArrowDown") player2Down = true;
        });

        document.addEventListener("keyup", (e) => {
            if (e.key === "w") player1Up = false;
            if (e.key === "s") player1Down = false;
            if (e.key === "ArrowUp") player2Up = false;
            if (e.key === "ArrowDown") player2Down = false;
        });

        document.getElementById("start-button").addEventListener("click", () => {
            player1Score = 0;
            player2Score = 0;
            resetBall();
            gameLoop();
        });

        window.addEventListener("beforeunload", () => {
            // Send the current score to PHP for saving
            fetch('', {
                method: 'POST',
                body: new URLSearchParams({
                    player1Score: player1Score,
                    player2Score: player2Score
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('highscore-player1').textContent = data[0];
                document.getElementById('highscore-player2').textContent = data[1];
            });
        });
    </script>
</body>
</html>
