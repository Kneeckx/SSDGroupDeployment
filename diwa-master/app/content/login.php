<div class="row">
    <div class="col-lg-12">
        <?php
        // CSRF token generation
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if(isset($_POST['email']) && isset($_POST['password'])) {
            // CSRF token validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo '<div class="alert alert-danger">Invalid CSRF token. Please reload the page and try again.</div>';
            } else {
                try {
                    // login
                    if($result = $model->userSignIn($_POST['email'], $_POST['password'], $config['system']['hashing_algorithm'])) {
                        if(false !== $result && 0 < count($result)) {
                            // delete session data
                            session_unset();

                            // save user to session
                            $_SESSION['user_id'] = $result[0]['id'];
                        }
                        redirect('?page=loggedin');
                    }
                    else {
                        echo '<div class="alert alert-danger">Wrong E-Mail-Address or Password</div>';
                    }
                }
                catch (Exception $ex) {
                    error(500, 'Exception during login', $ex);
                }
            }
        }
        ?>
    </div>
        <form method="post" action="?page=login">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="email">Email address:</label>
                <input type="email" class="form-control" name="email" id="email">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
</div>