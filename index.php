<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (isset($_SESSION['username'])) {
     $currentUserId =  $_SESSION['user_id'];
}else{
    $currentUserId=0;
}

$servername = "scaleabledatabase.mysql.database.azure.com";
$database = "showvideodb";
$username = "adminzeeshan";
$password = "Zeeshan@786";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

    //query to fetch the all the videos and total likes and comments
    $query = "SELECT 
        videos.video_id,
        videos.title,
        videos.description,
        videos.video_url,
        videos.created_at AS video_created_at,
        users.user_id AS video_user_id,
        users.username AS video_owner_username,
        COUNT(DISTINCT likes.like_id) AS total_likes,
        COUNT(DISTINCT comments.comment_id) AS total_comments
        FROM videos
        LEFT JOIN users ON videos.user_id = users.user_id
        LEFT JOIN likes ON videos.video_id = likes.video_id
        LEFT JOIN comments ON videos.video_id = comments.video_id
        GROUP BY videos.video_id
        ORDER BY videos.created_at DESC";
    $result = $conn->query($query);

    // Fetch comments with user details
        $sql_comments = "SELECT 
            comments.comment_id,
            comments.comment_text,
            comments.created_at AS comment_created_at,
            comments.video_id,
            users.user_id AS comment_user_id,
            users.username AS comment_user_username
            FROM comments
            LEFT JOIN users ON comments.user_id = users.user_id;";
        $comments = $conn->query($sql_comments);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Reels Page</title>
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
            height: 100vh;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
            overflow-y: scroll; /* Enable scrolling */
            scrollbar-width: none; /* Hide scrollbar for Firefox */
            -ms-overflow-style: none; /* Hide scrollbar for Edge and IE */
        }

        .video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .top-icons {
            position: fixed;
            top: 10px;
            right: 10%;
            display: flex;
            gap: 15px;
        }
        .username-display {
            display: block;
            font-size: 14px;
            color: white;
            text-align: center;
            margin-top: 5px;
        }

        .volume-icon {
            position: absolute;
            top: 10px;
            left: 10px;
            background: none;
            border: none;
            color: #fff;
            font-size: 36px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .volume-icon:hover {
            transform: scale(1.2);
        }

        .top-icons .icon-button {
            background: none;
            border: none;
            color: #fff;
            font-size: 36px;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-align: center;
        }

        .top-icons .icon-button:hover {
            transform: scale(1.2);
        }

        .top-icons .file-input {
            display: none;
        }

        .overlay {
            position: absolute;
            bottom: 110px;
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
            position: relative;
            bottom: 420px;
            left: 45%;
            transform: translateX(-50%);
            max-width: 400px;
            width: 90%;
            background: rgba(0, 0, 0, 0.8);
            padding: 10px;
            border-radius: 8px;
            display: none;
            flex-direction: column;
            gap: 10px;
        }

        .comment-box textarea {
            padding: 10px;
            border: none;
            border-radius: 4px;
            resize: none;
            font-size: 14px;
        }

        .comment-box .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-direction: row;
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

        .count-text {
            font-size: 14px;
            color: #fff;
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

        .logout-button {
            background: none;
            border: none;
            color: #fff;
            font-size: 36px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .logout-button:hover {
            transform: scale(1.2);
        }

        @media (max-width: 500px) {
            .title {
                font-size: 14px;
            }
        }
        .video-container {
            position: relative;
            width: 450px;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #333;
            scroll-snap-align: start;
        }
        #video-preview {
            border: 1px solid #555;
            border-radius: 8px;
            background-color: black;
            max-height: 250px;
        }

        .video-details{
            position: absolute;
            bottom: 0px;
            left: 0px;
        }

        .reel-container::-webkit-scrollbar {
            display: none; /* Hide scrollbar for WebKit browsers */
        }
        .fas, .count-text, .video-details {
            text-shadow: 8px 8px 15px rgba(0, 0, 0, 0.5);
        }


    </style>
</head>
<body>
    <div class="main-container">
        <div class="reel-container">
            <div class="top-icons">
                <button id="profile-icon" class="icon-button" onclick="toggleModal()">
                    <i class="fas fa-user-circle"></i>
                </button>
                <span id="username-display" style="margin-right: 10px; font-size: 16px; color: white; display: none;">User</span>
                <button class="icon-button" onclick="logout()" id="logout-button" style="display: none;">
                    <i class="fas fa-power-off"></i>
                </button>
                <label id="upload-icon" class="icon-button" onclick="openFileUploadModal()" for="file-upload" style="display: none;">
                    <i class="fas fa-upload"></i>
                </label>
            </div>

            <?php
            
            if ($result->num_rows > 0) {
                // Iterate through the results
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="video-container">';
                    echo '<video class="video" autoplay loop onclick="togglePlayPause(this)">';
                    echo '<source src="' . $row['video_url'] . '" type="video/mp4">';
                    echo '</video>';
                    echo '<div class="video-details">';
                    echo '<h3>' .$row['title'] . '</h3>';
                    echo '<p>@: ' . $row['video_owner_username'] . '</p>';
                    echo '<div class="overlay">
                            <div class="actions">
                                <button class="action-button" onclick="likeVideo(' . $currentUserId . ',' . $row['video_id'] . ', toggleLike(this))">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="count-text" id="like-count-' . $row['video_id'] . '">' . $row['total_likes'] . '</div>
                                <button class="action-button" onclick="toggleCommentBox(' . $row['video_id'] . ')">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <div class="count-text" id="comment-count">' . $row['total_comments'] . '</div>
                            </div>
                        </div>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="comment-box" id="comment-box-' . $row['video_id'] . '">';
                        // Check if there are comments
                            if (!empty($comments)) {
                                // Loop through the results and echo the details
                                foreach ($comments as $comment) {
                                    if($row['video_id'] == $comment['video_id'])
                                    echo "<label>". $comment['comment_text'] ." @ ".$comment['comment_user_username']."</label>";
                                }
                            } else {
                                echo "<label>No Comments</label>";
                            }
                                echo '<textarea rows="3" id="videoComment-' . $row['video_user_id'] . '" placeholder="Write a comment..."></textarea>
                                    <div class="actions">
                                        <button class="close-button" onclick="toggleCommentBox(' . $row['video_id'] . ')">Close</button>
                                        <button onclick="submitComment(' . $currentUserId . ',' . $row['video_id'] . ')">Submit</button>
                                    </div>
                                </div>';
                }
            } else { ?>
                No Video Found
            <?php  }
                $conn->close();
            ?>
        </div>

    </div>

    <!-- Modal -->
    <div class="modal" id="profile-modal">
        <div class="modal-content" id="modal-content">
            <h2>Login</h2>
                <form id="login-form" action="http://localhost/my-app/login.php" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Sign In</button>
                </form>
            <span class="toggle-link" onclick="toggleForm()">Don't have an account? Register</span>
            <button class="close-modal" onclick="toggleModal()">Close</button>
        </div>
    </div>

     <!-- File Upload Modal -->
     <div class="modal" id="file-upload-modal">
    <div class="modal-content">
        <h2>Upload Video</h2>
        <form id="file-upload-form" enctype="multipart/form-data" method="POST">
            <!-- Include 'name' attributes for proper form data submission -->
            <input type="text" id="video-title" name="title" placeholder="Enter video title" required>
            <input type="file" id="video-file" name="video" accept="video/*" required>
            
            <!-- Video preview -->
            <video id="video-preview" controls style="display:none; width:100%; margin-top:10px;"></video>
            
            <!-- Modal buttons -->
            <div style="margin-top: 10px;">
                <button type="button" class="close-modal" onclick="closeFileUploadModal()">Close</button>
                <button type="submit">Upload</button>
            </div>
        </form>
    </div>
</div>



    <script>

        // video like function
        async function likeVideo(userId, videoId) {
            if (userId === 0) {
                alert('You need to login first!');
                return;
            }

            try {
                const response = await fetch("like.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ video_id: videoId, user_id: userId }),
                });

                const result = await response.json();

                if (result.success) {

                    // Get the string value from the element
                    const likeElement = document.getElementById('like-count-'+videoId);
                    const currentlikes = likeElement.innerText;
                    const numericValue = parseFloat(currentlikes);
                    // Perform addition
                    const newLikes = numericValue + 1;
                    likeElement.innerText = newLikes;
                } else {
                    alert(result.message || "An error occurred.");
                }
            } catch (error) {
                console.error("Error:", error);
            }
        }

        //--------------
        function toggleCommentBox(boxId) {
            const commentBox = document.getElementById('comment-box-'+boxId);
            commentBox.style.display = commentBox.style.display === 'flex' ? 'none' : 'flex';
        }

        //--------------------
        async function submitComment(userId, videoId) {
            if (userId === 0) {
                alert('You need to login first!');
                return;
            }
            // alert('user id: '+userId+'  video id: '+videoId);
            const commentBox = document.getElementById("comment-box-"+videoId);
            const commentText = commentBox.querySelector('textarea').value;

            if (!commentText) {
                alert('Comment cannot be empty!');
                return;
            }

            if (commentText.length > 250) {
                alert('Comment cannot exceed 250 characters.');
                return;
            }

            try {
                const response = await fetch('comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        comment_text: commentText,
                        video_id: videoId,
                        user_id: userId,
                    }),
                });

                const result = await response.json();
                if (result.success) {
                    toggleCommentBox(videoId);
                } else {
                    alert(`Error: ${result.message}`);
                }
            } catch (error) {
                console.error('Error submitting comment:', error);
                alert('Something went wrong. Please try again.');
            }
        }

        //-------------
        function toggleModal() {
            const modal = document.getElementById('profile-modal');
            modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
        }
        //---------------
        function toggleForm() {
            const modalContent = document.getElementById('modal-content');
            const isLogin = modalContent.querySelector('h2').innerText === 'Login';

            modalContent.innerHTML = isLogin ? `
                <h2>Register</h2>
                <form id="register-form" action="register.php" method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Register</button>
                </form>

                <span class="toggle-link" onclick="toggleForm()">Already have an account? Login</span>
                <button class="close-modal" onclick="toggleModal()">Close</button>
            ` : `
                <h2>Login</h2>
                <form id="login-form" action="http://localhost/my-app/login.php" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Sign In</button>
                </form>
                <span class="toggle-link" onclick="toggleForm()">Don't have an account? Register</span>
                <button class="close-modal" onclick="toggleModal()">Close</button>
            `;
        }
        //------------
        function togglePlayPause(videoElement) {
            if (videoElement.paused) {
                videoElement.play();
            } else {
                videoElement.pause();
            }
        }
        //-----login 
        document.getElementById("login-form").addEventListener("submit", async (event) => {
            event.preventDefault();
            const formData = new FormData(document.getElementById('login-form'));
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    toggleModal(); // Close the modal
                    document.getElementById('username-display').textContent = result.username; // Display username
                    updateUserUI(result.username);
                } else {
                    alert(result.message || 'Login failed.');
                }
            })
            .catch(error => {
                console.error('Error during login:', error);
                alert('An error occurred during login.');
            });
        });


        //------ logout 
        // Function to show/hide user-specific elements
        function updateUserUI(username) {
        const usernameDisplay = document.getElementById('username-display');
        const logoutButton = document.getElementById('logout-button');
        const profileIcon = document.getElementById('profile-icon');
        const uploadIcon = document.getElementById('upload-icon');

        if (username) {
            usernameDisplay.textContent = username;
            usernameDisplay.style.display = 'inline';
            logoutButton.style.display = 'inline';
            profileIcon.style.display = 'none'; // profile icon
            uploadIcon.style.display = "inline"// show upload icon
        } else {
            usernameDisplay.textContent = '';
            usernameDisplay.style.display = 'none';
            logoutButton.style.display = 'none';
            profileIcon.style.display = 'inline'; // profile icon
            uploadIcon.style.display = "none"// show upload icon
        }
    }

        // Logout function
        function logout() {
            fetch('logout.php', { method: 'POST' })
                .then(response => {
                    if (response.ok) {
                        updateUserUI(false); // Hide user-specific elements
                        // alert('You have logged out successfully.');
                    } else {
                        alert('Error logging out.');
                    }
                })
                .catch(error => console.error('Logout error:', error));
        }

        //--------- on load check if user is logged in or not
        window.onload = function () {
            fetch('login_check.php')
                .then(response => response.json())
                .then(result => {
                    if (result.loggedIn) {
                        updateUserUI(result.username);
                    }
                })
                .catch(error => console.error('Error fetching session data:', error));
        };

        // Open File Upload Modal
        function openFileUploadModal() {
            document.getElementById('file-upload-modal').style.display = 'flex';
        }

        // Close File Upload Modal
        function closeFileUploadModal() {
            document.getElementById('file-upload-modal').style.display = 'none';
            document.getElementById('video-title').value = '';
            document.getElementById('video-file').value = '';
            const preview = document.getElementById('video-preview');
            preview.style.display = 'none';
            preview.src = '';
            URL.revokeObjectURL(preview.src);
        }

        // Handle File Selection and Preview
        document.getElementById('video-file').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!['video/mp4', 'video/avi', 'video/mkv', 'video/mov'].includes(file.type)) {
                alert('Invalid file type. Please upload a valid video file.');
                event.target.value = '';
                return;
            }

            if (file.size > 1 * 1024 * 1024) {
                alert('File size exceeds 1 MB. Please upload a smaller file.');
                event.target.value = '';
                return;
            }

            const preview = document.getElementById('video-preview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        });

        // Handle File Upload Form Submission
        document.getElementById('file-upload-form').addEventListener('submit', function (event) {
            event.preventDefault();

            const title = document.getElementById('video-title').value.trim();
            const file = document.getElementById('video-file').files[0];

            if (!title || !file) {
                alert('Please provide both a title and a video file.');
                return;
            }

            const formData = new FormData();
            formData.append('title', title);
            formData.append('video', file);

            fetch('upload.php', { method: 'POST', body: formData })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        alert('Video uploaded successfully!');
                        closeFileUploadModal();
                    } else {
                        alert('Failed to upload video: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('An error occurred during upload.');
                });
        });

        ///------------
        function toggleLike(button) {
            const icon = button.querySelector('i');
            icon.classList.toggle('liked');
        }


    </script>
</body>
</html>