<?php
// make sure only registered users can enter this site
if(isset($_SESSION['user_id'])) {
    echo getForbiddenMessage('You already are a registered User.');
    return;
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = array();

// process post data
if('post' === strtolower($_SERVER['REQUEST_METHOD']) && isset($_POST)) {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token. Please reload the page and try again.';
    }
    if(!isset($_POST['username']) || strlen(trim($_POST['username'])) < 3) {
        $errors[] = 'Your Username must be at least 3 non-space characters.';
    } elseif (preg_match('/^\s+$/', $_POST['username'])) {
        $errors[] = 'Your Username cannot be only spaces.';
    } else {
        // validate email-address
        if(!isset($_POST['email']) || false === filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid Email address.';
        } else {

            // find out if email-address already is in use
            try {
                if ($model->isUserEmailInUse($_POST['email'])) {
                    $errors[] = 'The E-Mail-Address "' . $_POST['email'] . '" already is in use. Please choose a different one.';
                }
            } catch (Exception $ex) {
                error(500, 'Exception while finding a User', $ex);
            }
        }

        // find out if username already is in use
        try {
            if($model->isUsernameInUse($_POST['username'])) {
                $errors[] = 'The Username "' . $_POST['username'] . '" already is in use. Please choose a different one.';
            }
        }
        catch (Exception $ex) {
            error(500, 'Exception while finding a User', $ex);
        }
    }

    if(!empty($_POST['password'])) {
        $password = $_POST['password'];
        if (strlen(trim($password)) < 8) {
            $errors[] = 'Your Password must be at least 8 characters.';
        } elseif (preg_match('/^\s+$/', $password)) {
            $errors[] = 'Your Password cannot be only spaces.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Your Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Your Password must contain at least one lowercase letter.';
        } elseif (!preg_match('/\d/', $password)) {
            $errors[] = 'Your Password must contain at least one digit.';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Your Password must contain at least one special character.';
        } else {
            // do passwords match?
            if ($password !== $_POST['password-repeat']) {
                $errors[] = 'The passwords do not match.';
            }
        }
    } else {
        $errors[] = 'Please enter a Password.';
    }

    // Registration Code correct?
    if($config['site']['invitation_code'] != $_POST['invitation-code']) {
        $errors[] = 'The Invitation Code was wrong.';
    }

    if(empty($errors)) {
        try {
            if(!$model->createUser($_POST['username'], $_POST['password'], $_POST['email'], $_POST['country'], $config['system']['hashing_algorithm'])) {
                $errors[] = 'Your Registration was not successfull.';
            } else {
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
        }
        catch(Exception $ex) {
            error(500, 'The registration was not successfull', $ex);
        }
    }
}

// list of all countries
$countryList = array('Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegowina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo, the Democratic Republic of the', 'Cook Islands', 'Costa Rica', 'Cote d\'Ivoire', 'Croatia (Hrvatska)', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'France Metropolitan', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard and Mc Donald Islands', 'Holy See (Vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran (Islamic Republic of)', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, Democratic People\'s Republic of', 'Korea, Republic of', 'Kuwait', 'Kyrgyzstan', 'Lao, People\'s Democratic Republic', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau', 'Macedonia, The Former Yugoslav Republic of', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia, Federated States of', 'Moldova, Republic of', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia (Slovak Republic)', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands', 'Spain', 'Sri Lanka', 'St. Helena', 'St. Pierre and Miquelon', 'Sudan', 'Suriname', 'Svalbard and Jan Mayen Islands', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan, Province of China', 'Tajikistan', 'Tanzania, United Republic of', 'Thailand', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela', 'Vietnam', 'Virgin Islands (British)', 'Virgin Islands (U.S.)', 'Wallis and Futuna Islands', 'Western Sahara', 'Yemen', 'Yugoslavia', 'Zambia', 'Zimbabwe');

?>

<div class="row">
    <div class="col-lg-12">
        <h1>User Registration</h1>
        <?php
        if(!empty($errors)) {
            echo '<div class="alert alert-danger">' . implode('<br/>', $errors) . '</div>';
        }
        ?>
        <p class="text-center">
        <form method="post" action="?page=register">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" value="<?php echo (isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''); ?>" id="username">
            </div>
            <div class="form-group">
                <label for="email">Email address:</label>
                <input type="email" class="form-control" name="email" value="<?php echo (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>" id="email">
            </div>
            <div class="form-group">
                <label for="country">Location:</label>
                <select name="country" class="form-control" id="country">
                    <?php
                    foreach ($countryList as $country) {
                        $safeCountry = htmlspecialchars($country);
                        $selected = (isset($_POST['country']) && $_POST['country'] == $country) ? ' selected="selected"' : '';
                        echo '<option value="' . $safeCountry . '"' . $selected . '>' . $safeCountry . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>
            <div class="form-group">
                <label for="password-repeat">Repeat Password:</label>
                <input type="password" class="form-control" name="password-repeat" id="password-repeat">
            </div>
            <hr/>
            <div class="form-group">
                <div class="alert alert-warning">
                    <label for="invitation-code"><?php echo icon('lock'); ?> Invitation Code (4 Digits):</label>
                    <input type="password" class="form-control" name="invitation-code" id="invitation-code" maxlength="4">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo icon('ok'); ?> Register</button>
        </form>
        </p>
    </div>
</div>