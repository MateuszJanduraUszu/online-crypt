<?php declare(strict_types = 1);
// userbase.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    $_Root_path = dirname(__FILE__, 2);
    require_once $_Root_path . "/account/token.php";
    require_once $_Root_path . "/cvt/hex.php";
    require_once $_Root_path . "/hash/md5.php";

    use mysqli;

    class account_data {
        function __construct(string $_Login = "", string $_Password = "", bool $_Active = false,
            token $_Token = new token(), string $_Salt = "") {
            $this->login    = $_Login;
            $this->password = $_Password;
            $this->active   = $_Active;
            $this->token    = new token($_Token->value());
            $this->salt     = $_Salt;
        }

        public string $login;
        public string $password;
        public bool $active;
        public token $token;
        public string $salt; // 16-byte unique random value
    }

    class userbase {
        static function current() : userbase {
            static $_Obj = new userbase;
            if (!$_Obj->is_open()) { // open after initialization
                $_Obj->open();
            }

            return $_Obj;
        } 
        
        private function __construct() {
            $this->_Myok    = false;
            $this->_Mytable = "";
        }

        function __destruct() {
            if ($this->is_open()) {
                $this->close();
            }
        }

        function open(string $_Name = "online_crypt", string $_Table = "users",
            string $_Host = "localhost", string $_Login = "root", string $_Password = null) : bool {
            if ($this->is_open()) { // some userbase is already open
                return false;
            }

            $this->_Myhandle = new mysqli($_Host, $_Login, $_Password);
            if ($this->_Myhandle->connect_errno != 0) {
                return false;
            }

            $this->_Myok = true;
            $_Command    = "USE " . $_Name . ";";
            if (!$this->_Myhandle->query($_Command)) { // database not found
                $this->close();
                return false;
            }

            $this->_Mytable = $_Table;
            return true;
        }

        function close() : void {
            if ($this->_Myok) {
                $this->_Myhandle->close();
                $this->_Myok    = false;
                $this->_Mytable = "";
            }
        }

        function is_open() : bool {
            return $this->_Myok;
        }

        function has_account(string $_Login) : bool {
            if (!$this->is_open()) { // no userbase is open
                return false;
            }

            $_Command = 'SELECT * FROM ' . $this->_Mytable . ' WHERE BINARY login = "' . $_Login . '";';
            return $this->_Myhandle->query($_Command)->num_rows > 0;
        }

        function load_account_data(string $_Login) : account_data | null {
            if (!$this->is_open()) { // no userbase is open
                return null;
            }

            $_Query = $this->_Myhandle->query(
                'SELECT * FROM ' . $this->_Mytable . ' WHERE BINARY login = "' . $_Login . '";');
            if ($_Query->num_rows == 0) { // account not found
                return null;
            }

            $_Row = $_Query->fetch_row();
            return new account_data(
                $_Row[0], $_Row[1], boolval($_Row[2]), new token(intval($_Row[3])), $_Row[4]);
        }

        function generate_unique_salt(int $_Size = 16) : string | null {
            if (!$this->is_open()) { // no userbase is open
                return null;
            }

            $_Salts = $this->_Load_all_salts();
            $_Salt  = "";
            for (;;) { // keep generating until the salt is unique
                $_Salt = generate_salt($_Size);
                if (!in_array(hex::encode($_Salt), $_Salts)) { // generated unique salt, use it
                    break;
                }
            }

            return $_Salt;
        }

        function create_account(account_data $_Data) : bool {
            if ($this->has_account($_Data->login)) { // account already exists
                return false;
            }

            if ($_Data->salt == null) { // generate a new salt
                $_Data->salt = $this->generate_unique_salt();
            }

            $_Data->password = md5($_Data->password, $_Data->salt, 16); // default salt size (16 bytes)
            $_Data->salt     = hex::encode($_Data->salt); // store as a hexadecimal string
            return $this->_Myhandle->query(
                'INSERT INTO ' . $this->_Mytable . ' (login, password, active, token, salt) VALUES ("'
                    . $_Data->login . '", "' . $_Data->password . '", ' . $_Data->active . ', '
                        . $_Data->token->to_string() . ', "' . $_Data->salt . '");');
        }

        function delete_account(string $_Login) : bool {
            if (!$this->has_account($_Login)) { // account not found, do nothing
                return true;
            }

            return $this->_Myhandle->query(
                'DELETE FROM ' . $this->_Mytable . ' WHERE BINARY login = "' . $_Login . '";');
        }

        function change_account_login(string $_Login, string $_New_login) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            if ($_Login == $_New_login) { // nothing has changed, do nothing
                return true;
            }
 
            return $this->_Myhandle->query(
                'UPDATE ' . $this->_Mytable . ' SET login = "' . $_New_login
                    . '" WHERE BINARY login = "' . $_Login . '";');
        }

        function change_account_password(string $_Login, string $_New_password) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            $_Data         = $this->load_account_data($_Login);
            $_Salt         = hex::decode($_Data->salt);
            $_New_password = md5($_New_password, $_Salt, 16); // default salt size (16 bytes)
            if ($_New_password == $_Data->password) { // nothing has changed, do nothing
                return true;
            }
 
            return $this->_Myhandle->query(
                'UPDATE ' . $this->_Mytable . ' SET password = "' . $_New_password
                    . '" WHERE BINARY login = "' . $_Login . '";');
        }

        function activate_account(string $_Login) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            return $this->_Myhandle->query(
                'UPDATE ' . $this->_Mytable . ' SET active = true WHERE BINARY login = "' . $_Login . '";');
        }

        function deactivate_account(string $_Login) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            return $this->_Myhandle->query(
                'UPDATE ' . $this->_Mytable . ' SET active = false WHERE BINARY login = "' . $_Login . '";');
        }

        function change_account_token(string $_Login, token $_New_token) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }
            
            return $this->_Myhandle->query(
                'UPDATE ' . $this->_Mytable . ' SET token = ' . $_New_token->to_string()
                    . ' WHERE BINARY login = "' . $_Login . '";');
        }

        private function _Load_all_salts() : array {
            if (!$this->is_open()) { // no userbase is open
                return array();
            }

            $_Query = $this->_Myhandle->query("SELECT salt FROM " . $this->_Mytable . " ORDER BY login;");
            $_Count = $_Query->num_rows;
            if ($_Count == 0) { // salt not found
                return array();
            }

            $_Result = array_fill(0, $_Count, "");
            for ($_Idx = 0; $_Idx < $_Count; ++$_Idx) {
                $_Row           = $_Query->fetch_row();
                $_Result[$_Idx] = $_Row[0];
            }

            return $_Result;
        }

        private mysqli $_Myhandle; // handle to the current userbase
        private string $_Mytable; // the current table name
        private bool $_Myok; // true if the userbase is open
    }
} // namespace mjx
?>