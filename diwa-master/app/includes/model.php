<?php

class Model {

    private $db = null;
    private $prefix = null;

    public function __construct($pDatabase, $pPrefix) {
        $this->db = $pDatabase;
        $this->prefix = $pPrefix;
    }

    // ============================
    // USERS
    // ============================

    public function userSignIn($pEmail, $pPassword, $pHashingAlgorithm) {
        try {
                $stmt = $this->db->prepare('SELECT * FROM ' . $this->prefix . 'users WHERE email = ? AND password = ?');
                $stmt->execute([$pEmail, hash($pHashingAlgorithm, $pPassword)]);
                return $stmt->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function isUserEmailInUse($pEmail) {
        try {
                $stmt = $this->db->prepare('SELECT * FROM ' . $this->prefix . 'users WHERE email = ?');
                $stmt->execute([$pEmail]);
                $result = $stmt->fetchAll();
                return (false !== $result && 0 < count($result));
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function isUsernameInUse($pUsername) {
        try {
                $stmt = $this->db->prepare('SELECT * FROM ' . $this->prefix . 'users WHERE username = ?');
                $stmt->execute([$pUsername]);
                $result = $stmt->fetchAll();
                return (false !== $result && 0 < count($result));
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function createUser($pUsername, $pPassword, $pEmail, $pCountry, $pHashingAlgorithm) {
        try {
                $stmt = $this->db->prepare('INSERT INTO ' . $this->prefix . 'users (username, password, email, country, is_admin) VALUES (?, ?, ?, ?, 0)');
                $result = $stmt->execute([
                    $pUsername,
                    hash($pHashingAlgorithm, $pPassword),
                    $pEmail,
                    $pCountry
                ]);
                return $result;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getUserData($pUserId) {
        try {
              $stmt = $this->db->prepare('SELECT * FROM ' . $this->prefix . 'users WHERE id = ?');
              $stmt->execute([$pUserId]);
              return $stmt->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function editUser($pUserId, $pEmail, $pCountry, $pChangePassword, $pChangeAdmin) {
        try {
            $fields = [];
            $params = [];
            if ($pEmail !== null) {
                $fields[] = 'email = ?';
                $params[] = $pEmail;
            }
            if ($pCountry !== null) {
                $fields[] = 'country = ?';
                $params[] = $pCountry;
            }
            if ($pChangePassword !== null) {
                $fields[] = 'password = ?';
                $params[] = $pChangePassword;
            }
            if ($pChangeAdmin !== null) {
                $fields[] = 'is_admin = ?';
                $params[] = $pChangeAdmin;
            }
            $params[] = $pUserId;
            $sql = 'UPDATE ' . $this->prefix . 'users SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            return $result;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    // ============================
    // DOWNLOADS
    // ============================

    public function getDownloads($pApproved) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM ' . $this->prefix . 'downloads WHERE approved = ?');
            $stmt->execute([$pApproved ? 1 : 0]);
            return $stmt->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
}

    public function createDownload($pAllowGuests, $pApproved, $pTitle, $pDescription, $pFile) {
        try {
            $stmt = $this->db->prepare('INSERT INTO ' . $this->prefix . 'downloads (allow_guests, approved, title, description, file) VALUES (?, ?, ?, ?, ?)');
            $result = $stmt->execute([
                $pAllowGuests ? 1 : 0,
                $pApproved ? 1 : 0,
                $pTitle,
                $pDescription,
                $pFile
            ]);
            return $result;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function approveDownload($pId, $pAllowGuests) {
        try {
            $stmt = $this->db->prepare('UPDATE ' . $this->prefix . 'downloads SET approved = 1, allow_guests = ? WHERE id = ?');
            $result = $stmt->execute([
                $pAllowGuests ? 1 : 0,
                $pId
            ]);
            return $result;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function removeDownload($pId) {
        try {
            $stmt = $this->db->prepare('DELETE FROM ' . $this->prefix . 'downloads WHERE id = ?');
            $result = $stmt->execute([$pId]);
            return $result;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    // ============================
    // BOARD
    // ============================

    public function getAllThreads() {
        try {
            $sql = 'SELECT t.id AS id, t.title AS title, t.admins_only AS admins_only, MAX(p.timestamp) AS last_post, COUNT(*) AS count_post FROM ' . $this->prefix . 'threads t, ' . $this->prefix . 'posts p WHERE p.thread_id = t.id GROUP BY t.id ORDER BY last_post DESC';
            return $this->db->query($sql)->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getThread($pThreadId) {
        try {
            $stmt = $this->db->prepare('SELECT t.id AS id, t.title AS title, t.admins_only AS admins_only, MAX(p.timestamp) AS last_post, COUNT(*) AS count_post FROM ' . $this->prefix . 'threads t, ' . $this->prefix . 'posts p WHERE t.id = ? AND p.thread_id = t.id GROUP BY t.id');
            $stmt->execute([$pThreadId]);
            return $stmt->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getPosts($pThreadId) {
        try {
            $stmt = $this->db->prepare('SELECT p.*, u.username FROM ' . $this->prefix . 'posts p, ' . $this->prefix . 'users u WHERE p.thread_id = ? AND p.user_id = u.id ORDER BY p.id ASC');
            $stmt->execute([$pThreadId]);
            return $stmt->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function createThread($pTitle, $pAdminsOnly) {
        try {
            $stmt = $this->db->prepare('INSERT INTO ' . $this->prefix . 'threads (title, admins_only) VALUES (?, ?)');
            $result = $stmt->execute([
                $pTitle,
                $pAdminsOnly ? 1 : 0
            ]);
            if($result) {
                return $this->db->lastInsertId();
            }
            return false;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function createPost($pThreadId, $pUserId, $pText) {
        try {
            $stmt = $this->db->prepare('INSERT INTO ' . $this->prefix . 'posts (thread_id, user_id, text) VALUES (?, ?, ?)');
            $result = $stmt->execute([
                $pThreadId,
                $pUserId,
                $pText
            ]);
            return $result;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getPost($pPostId) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM ' . $this->prefix . 'posts WHERE id = ?');
            $stmt->execute([$pPostId]);
            return $stmt->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function editPost($pPostId, $pPost) {
        try {
            $stmt = $this->db->prepare('UPDATE ' . $this->prefix . 'posts SET text = ? WHERE id = ?');
            $result = $stmt->execute([
                $pPost,
                $pPostId
            ]);
            return $result;
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getPostsByUser($pUserId) {
        try {
            $stmt = $this->db->prepare('SELECT t.id AS thread_id, t.title AS thread_title, t.admins_only AS thread_admins_only, p.id AS post_id, p.timestamp AS post_timestamp, p.text AS post_text FROM ' . $this->prefix . 'posts p, ' . $this->prefix . 'threads t WHERE p.user_id = ? AND p.thread_id = t.id ORDER BY p.timestamp DESC');
            $stmt->execute([$pUserId]);
            return $stmt->fetchAll();
        }
        catch(Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }
}