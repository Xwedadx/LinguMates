<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Previous Sessions</title>
    <link rel="stylesheet" href="PreviousSessionsPage.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="footer.css?v=<?php echo time(); ?>">
    <script src="https://kit.fontawesome.com/9eeac525af.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="big-wrapper light">
    <header>
        <div class="container">
            <div class="logo">
                <a href="NS_homepage.php">
                    <img src="logo.png" alt="Logo">
                </a>
            </div>
            <div class="links">
                <ul>
                    <li>
                        <a href="NativeProfilePage.html">
                            <?php 
                            $host = "localhost";
                            $dbname = "lingumatesdb";
                            $username = "root";
                            $password = "";
                            $mysqli = new mysqli($host, $username, $password, $dbname);
                            if ($mysqli->connect_error) {
                                die("Connection error: " . $mysqli->connect_errno);
                            }

                            if (isset($_SESSION['email'])) {
                                $email = $_SESSION['email'];

                                $photo = '';

                                if (!empty($email)) {
                                    $query = "SELECT photo FROM languagepartners WHERE email = ?";
                                    $stmt = $mysqli->prepare($query);
                                    if ($stmt) {
                                        $stmt->bind_param("s", $email);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        if ($result && $result->num_rows > 0) {
                                            $row = $result->fetch_assoc();
                                            $photo = $row['photo'];
                                        }
                                        $stmt->close();
                                    }
                                }
                            }
                            ?>
                            <img src="images/<?php echo htmlspecialchars($photo); ?>" alt="User" class="round-image">
                        </a>
                    </li>
                    <li><a href="SignOut.php">Sign out</a></li>
                </ul>
            </div>
        </div>
    </header>
    <div class="table-container">
        <?php
        $query = "SELECT partnerID FROM languagepartners WHERE email=?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $partnerID = $row['partnerID'];

                $currentDateTime = date("Y-m-d H:i:s");
                $querySessions = "SELECT s.*, r.language, l.firstName, l.lastName 
                                  FROM sessions s 
                                  JOIN requests r ON s.learnerID = r.learnerID 
                                  JOIN learners l ON s.learnerID = l.learnerID 
                                  WHERE s.partnerID = ? AND s.scheduledTime < ?";
                $stmtSessions = $mysqli->prepare($querySessions);
                if ($stmtSessions) {
                    $stmtSessions->bind_param("is", $partnerID, $currentDateTime);
                    $stmtSessions->execute();
                    $resultSessions = $stmtSessions->get_result();

                    if ($resultSessions->num_rows > 0) {
        ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Language of the Lesson</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Learner</th>
                                    <th>Review</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($rowSession = $resultSessions->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($rowSession['language']); ?></td>
                                        <td><?= htmlspecialchars($rowSession['scheduledTime']); ?></td>
                                        <td><?= htmlspecialchars($rowSession['duration']); ?></td>
                                        <td><?= htmlspecialchars($rowSession['firstName'] . ' ' . $rowSession['lastName']); ?></td>
                                        <td>
                                            <?php 
                                            $sessionID = $rowSession['sessionID'];
                                            $queryReview = "SELECT * FROM reviews_ratings WHERE sessionID = ?";
                                            $stmtReview = $mysqli->prepare($queryReview);
                                            if ($stmtReview) {
                                                $stmtReview->bind_param("i", $sessionID);
                                                $stmtReview->execute();
                                                $resultReview = $stmtReview->get_result();
                                                if ($resultReview && $resultReview->num_rows > 0) {
                                                    $rowReview = $resultReview->fetch_assoc();
                                                    echo htmlspecialchars($rowReview['review']);
                                                    $rating = $rowReview['rating']; // Fetch the rating from the database
                                                } else {
                                                    echo "Waiting for review";
                                                    $rating = 0; // Initialize rating
                                                }
                                                $stmtReview->close();
                                            }
                                            ?>
                                        </td>
                                        <td>
    <?php 
    // Check if 'stars' key exists in $rowReview array
    if (isset($rowReview['rating'])) {
        $rating = $rowReview['rating']; // Fetch the rating from the database
        // Display stars based on the rating
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                echo '<i class="fa-solid fa-star" style="color: #FFD43B;"></i>'; // Filled star
            } else {
                echo '<i class="fa-regular fa-star" style="color: #FFD43B;"></i>'; // Empty star
            }
        }
    } else {
        echo "Waiting for rating";
    }
    ?>
</td>

                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php
                    } else {
                        echo "<h2>You don't have any previous sessions</h2>";
                    }
                }
            } else {
                echo "<h2>Partner not found</h2>";
            }
            $stmt->close();
        } else {
            echo "<h2>Session ID not set</h2>";
        }
        ?>
    </div>
    <footer>
        <div class="footerContainer">
            <div class="socialicon">
                <a href="https://facebook.com"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com/?hl=ar"><i class="fab fa-instagram"></i></a>
                <a href="https://twitter.com"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://www.youtube.com/"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <div class="footerNav">
            <ul>
                <li><a href="aboutus.html">About us</a></li>
                <li><a href="mailto:lingumates@gmail.com">Contact us</a></li>
            </ul>
        </div>
    </footer>
    <div class="footerBottom">
        <p>&copy; LinguMates, 2024;  </p>
    </div>
</div>
</body>
</html>
