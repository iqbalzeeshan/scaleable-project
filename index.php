<?php
$servername = "scaleabledatabase.mysql.database.azure.com";
$database = "showvideodb"
$username = "adminzeeshan";
$password = "Zeeshan@786";

// Create connection
$conn = new mysqli($servername, $database, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Video App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #000;
            color: #fff;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .main-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reel-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            height: 100%;
            background-image: url('https://via.placeholder.com/500x800');
            background-size: cover;
            background-position: center;
        }

        .video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .top-icons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 15px;
        }

        .top-icons .icon-button {
            background: none;
            border: none;
            color: #fff;
            font-size: 36px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .top-icons .icon-button:hover {
            transform: scale(1.2);
        }

        .top-icons .file-input {
            display: none;
        }

        .overlay {
            position: absolute;
            bottom: 20px;
            left: 10px;
            right: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .title {
            max-width: 70%;
            font-size: 16px;
            line-height: 1.5;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
        }

        .actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .action-button {
            background: none;
            border: none;
            color: #fff;
            font-size: 32px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .action-button:hover {
            transform: scale(1.2);
        }

        .action-button i {
            font-size: 36px;
        }

        .liked {
            color: red;
        }

        .comment-box {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            max-width: 500px;
            width: 90%;
            background: rgba(0, 0, 0, 0.8);
            padding: 10px;
            border-radius: 8px;
            display: none;
            flex-direction: column;
            gap: 10px;
        }

        .comment-box textarea {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            resize: none;
            font-size: 14px;
        }

        .comment-box .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .comment-box button {
            padding: 8px 16px;
            background: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }

        .comment-box button:hover {
            background: #0056b3;
        }

        .close-button {
            background: #ff4d4d;
            color: white;
        }

        .close-button:hover {
            background: #cc0000;
        }

        .navigation-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            font-size: 24px;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
        }

        .navigation-button:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .prev-button {
            left: 0;
        }

        .next-button {
            right: 0;
        }

        .count-text {
            font-size: 14px;
            color: #ddd;
            margin-top: 5px;
            text-align: center;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }

        .modal-content {
            background: #222;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            color: white;
        }

        .modal-content h2 {
            margin-bottom: 20px;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .modal-content input {
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal-content button {
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-content .toggle-link {
            margin-top: 10px;
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
        }

        .modal-content .close-modal {
            background: #ff4d4d;
            color: white;
            margin-top: 10px;
        }

        .modal-content .close-modal:hover {
            background: #cc0000;
        }

        @media (max-width: 500px) {
            .title {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <button class="navigation-button prev-button" onclick="prevReel()">&lt;</button>

        <div class="reel-container">
            <video class="video" autoplay loop muted>
                <source src="example-video.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>

            <div class="top-icons">
                <button class="icon-button" onclick="toggleModal()">
                    <i class="fas fa-user-circle"></i>
                </button>
                <label class="icon-button" for="file-upload">
                    <i class="fas fa-upload"></i>
                </label>
                <input id="file-upload" type="file" class="file-input" onchange="handleFileUpload(event)">
            </div>

            <div class="overlay">
                <div class="title">This is the title of the reel</div>
                <div class="actions">
                    <button class="action-button" onclick="toggleLike(this)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="count-text" id="like-count">123 likes</div>
                    <button class="action-button" onclick="share()">
                        <i class="fas fa-share"></i>
                    </button>
                    <button class="action-button" onclick="toggleCommentBox()">
                        <i class="fas fa-comment"></i>
                    </button>
                    <div class="count-text" id="comment-count">45 comments</div>
                </div>
            </div>

            <div class="comment-box" id="comment-box">
                <textarea rows="3" placeholder="Write a comment..."></textarea>
                <div class="actions">
                    <button class="close-button" onclick="toggleCommentBox()">Close</button>
                    <button onclick="submitComment()">Submit</button>
                </div>
            </div>
        </div>

        <button class="navigation-button next-button" onclick="nextReel()">&gt;</button>
    </div>

    <!-- Modal -->
    <div class="modal" id="profile-modal">
        <div class="modal-content" id="modal-content">
            <h2>Login</h2>
            <form id="login-form">
                <input type="email" placeholder="Email" required>
                <input type="password" placeholder="Password" required>
                <button type="submit">Sign In</button>
            </form>
            <span class="toggle-link" onclick="toggleForm()">Don't have an account? Register</span>
            <button class="close-modal" onclick="toggleModal()">Close</button>
        </div>
    </div>

    <script>
        function toggleLike(button) {
            const icon = button.querySelector('i');
            icon.classList.toggle('liked');
        }

        function handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                alert(`File uploaded: ${file.name}`);
            }
        }

        function share() {
            alert('Share functionality coming soon!');
        }

        function toggleCommentBox() {
            const commentBox = document.getElementById('comment-box');
            commentBox.style.display = commentBox.style.display === 'flex' ? 'none' : 'flex';
        }

        function submitComment() {
            const commentBox = document.getElementById('comment-box');
            const comment = commentBox.querySelector('textarea').value;
            if (comment.trim()) {
                alert(`Comment submitted: ${comment}`);
                commentBox.querySelector('textarea').value = '';
                commentBox.style.display = 'none';
            } else {
                alert('Please write a comment before submitting.');
            }
        }

        function prevReel() {
            alert('Previous reel functionality coming soon!');
        }

        function nextReel() {
            alert('Next reel functionality coming soon!');
        }

        function toggleModal() {
            const modal = document.getElementById('profile-modal');
            modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
        }

        function toggleForm() {
            const modalContent = document.getElementById('modal-content');
            const isLogin = modalContent.querySelector('h2').innerText === 'Login';

            modalContent.innerHTML = isLogin ? `
                <h2>Register</h2>
                <form id="register-form">
                    <input type="text" placeholder="Username" required>
                    <input type="email" placeholder="Email" required>
                    <input type="password" placeholder="Password" required>
                    <button type="submit">Register</button>
                </form>
                <span class="toggle-link" onclick="toggleForm()">Already have an account? Login</span>
                <button class="close-modal" onclick="toggleModal()">Close</button>
            ` : `
                <h2>Login</h2>
                <form id="login-form">
                    <input type="email" placeholder="Email" required>
                    <input type="password" placeholder="Password" required>
                    <button type="submit">Sign In</button>
                </form>
                <span class="toggle-link" onclick="toggleForm()">Don't have an account? Register</span>
                <button class="close-modal" onclick="toggleModal()">Close</button>
            `;
        }
    </script>
</body>
</html>
