<?php

/**
 * @author Martin Borek <mborekcz@gmail.com>
 * @author Michal Dobes <michal.dobes.jr@gmail.com>
 */

require_once("Admin.php");
require_once("Customer.php");
require_once("Invoice.php");
require_once("Postman.php");
require_once("Publication.php");
require_once("Subscription.php");
require_once("Security.php");

/** Exception - Cannot connect to the DB or prepare SQL query */
class DBException extends Exception { }

/** Exception - Operation cannot be performed due to wrong inputs */
class DBExecuteException extends Exception { } 

/** Exception - Login exists, cannot be registered */
class DBLoginException extends Exception { }  

/** Binding parameters for SQL queries */
class BindParam { 
    private $values = array();
    private $types = ''; 
 
    /** add
     *
     *   Adds new parameter.
     * 
     * @param char $string Type of added parametr (i - integer, s - string)
     * @param pointer $value References parameter's value
     */
    public function add($type, &$value) { 
        $this->values[] = &$value; 
        $this->types .= $type; 
    } 
    
    /** get
     *
     *   Makes an array from merged types and values of parameters.
     * 
     * @return array()
     */
    public function get() { 
        return array_merge(array($this->types), $this->values); 
    } 
}


/** sqlPrepare
 *
 *   Prepares given string for SQL search regardless diacritics and capital
 * letters.
 *
 * @param string $string
 * @return string String in REGEX
 */
function sqlPrepare($string){
  $charTable[]='AÁÄaáä';
  $charTable[]='Bb';
  $charTable[]='CÈcè';
  $charTable[]='DÏdï';
  $charTable[]='EÉÌËeéìë';
  $charTable[]='Ff';
  $charTable[]='Gg';
  $charTable[]='Hh';
  $charTable[]='IÍií';
  $charTable[]='Jj';
  $charTable[]='Kk';
  $charTable[]='Ll';
  $charTable[]='Mm';
  $charTable[]='NÒnò';
  $charTable[]='OÓÖoóö';
  $charTable[]='Pp';
  $charTable[]='Qq';
  $charTable[]='RØrø';
  $charTable[]='S©s¹';
  $charTable[]='T«t»';
  $charTable[]='UÚÙÜuúùü';
  $charTable[]='Vv';
  $charTable[]='Ww';
  $charTable[]='Xx';
  $charTable[]='YÝyý';
  $charTable[]='Z®z¾';

  foreach($charTable as $char){
    $ereg='['.$char.']';
    $string = preg_replace("/$ereg/i",$ereg,$string);
  }

  return $string;
}


/** connectDB
 *
 *   Connects to DB and sets encoding. Throws DBException when
 * connection not established.
 *
 * @return mysqli Valid connection
 */
function connectDB() {
    $server = 'localhost';
    $login = 'xdobes13';
    $passwd = 'entu3ofa';
    $db = 'xdobes13';
//    $login = 'xborek08';
//    $passwd = 'omajrem5';
//    $db = 'xborek08';
    
    $mysqli = new mysqli($server, $login, $passwd, $db);

    if ($mysqli->connect_error) {
        throw new DBException('Cannot connect to DB');
    }

    if (!$mysqli->set_charset("latin2"))
        throw new DBException('Cannot set encoding.');

    return $mysqli;
}


/** authenticateUser
 * 
 *   If the password is correct, returns authentication level for 
 * the given user - AUTH_LVL_ADMIN/AUTH_LVL_POSTMAN (defined in
 * include/Security.php). If the password is incorrect, returns auth
 * level AUTH_LVL_NOBODY.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param string $username sanitized username
 * @param string $password UNSANITIZED password
 * @return AUTH_LVL
 */
function authenticateUser($username, $password) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("SELECT * FROM admin
            WHERE login=? AND password=md5(?)")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ss", $username, $password);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return AUTH_LVL_ADMIN;       
    }
    $stmt->close();
    
    if (!$stmt = $mysqli->prepare("SELECT * FROM postman
            WHERE login=? AND password=md5(?)")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ss", $username, $password);
    
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');
        
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $mysqli->close();
        return AUTH_LVL_POSTMAN;       
    }
 
    $mysqli->close();
    return AUTH_LVL_NOBODY;    
}


/** searchCustomers
 * 
 *   Returns a list of customers (instances of the Customer class) 
 * that fit given criteria. If all criteria are "", all customers are
 * returned.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @param int $id
 * @param string $name
 * @param string $surname
 * @param string $email
 * @param string $city
 * @param string $street
 * @param int $ZIP
 * @param int $postman
 * @return array(Customer)
 */
function searchCustomers($id = "", $name = "", $surname = "", $email = "",
        $city = "", $street = "", $ZIP = "", $postman = "") {
    $mysqli = connectDB();
    $result = array();

    $query = 'SELECT * FROM customer'; 
    if (empty($id) && empty($name) && empty($surname) && empty($email) &&
            empty($city) && empty($street) && empty($ZIP) && empty($postman)) {
        if (!$stmt = $mysqli->prepare($query)) {
            throw new DBException('Wrong DB query');
        }
    }
    elseif (!empty($id)) {
        $query .= ' WHERE ';
        if (!$stmt = $mysqli->prepare($query."customer_id=?")) {
            throw new DBException('Wrong DB query');
        }
        $stmt->bind_param("i", $id);
    }
    else {
        $query .= ' WHERE ';
        $bindParam = new BindParam(); 
        $qArray = array(); 

        if (!empty($name)) { 
            $qArray[] = 'name REGEXP ?'; 
            $name = sqlPrepare($name);
            $bindParam->add('s', $name); 
        }
        if (!empty($surname)) { 
            $qArray[] = 'surname REGEXP ?'; 
            $surname = sqlPrepare($surname);
            $bindParam->add('s', $surname); 
        }
        if (!empty($email)) { 
            $qArray[] = 'email REGEXP ?'; 
            $email = sqlPrepare($email);
            $bindParam->add('s', $email); 
        }
        if (!empty($city)) { 
            $qArray[] = 'city REGEXP ?'; 
            $city = sqlPrepare($city);
            $bindParam->add('s', $city); 
        }
        if (!empty($street)) { 
            $qArray[] = 'street REGEXP ?'; 
            $street = sqlPrepare($street);
            $bindParam->add('s', $street); 
        }
        if (!empty($ZIP)) { 
            $qArray[] = 'zip=?'; 
            $bindParam->add('i', $ZIP); 
        }
        if (!empty($postman)) { 
            $qArray[] = 'postman_id=?'; 
            $bindParam->add('i', $postman); 
        }

        $query .= implode(' AND ', $qArray); 

        if (!$stmt = $mysqli->prepare($query)) {
            throw new DBException('Wrong DB query');
        }
        call_user_func_array(array($stmt, 'bind_param'), $bindParam->get()); 
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');


    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch()) {
        $result[] = new Customer($row['customer_id'], $row['name'],
                $row['surname'], $row['email'], $row['city'], $row['street'],
                $row['zip'], $row['postman_id'], $row['inactive_since'],
                $row['inactive_till']);
    }

    $mysqli->close();
    return $result;
}


/** searchPostmen
 * 
 *   Returns a list of postmen (instances of the Postman class) 
 * that fit given criteria. If $id=="", all postmen are returned.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param int $id Postman id
 * @return array(Postman)
 */
function searchPostmen($id = "") {
    $mysqli = connectDB();
    $result = array();

    if (empty($id)) {
        if (!$stmt = $mysqli->prepare("SELECT * FROM postman")) {
            throw new DBException('Wrong DB query');
        }
    }
    else{ 
        if (!$stmt = $mysqli->prepare("SELECT * FROM postman
                WHERE postman_id = ?")) {
            throw new DBException('Wrong DB query');
        }
        
        $stmt->bind_param("i", $id);
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }

    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch()) {
        $result[] = new Postman($row['postman_id'], $row['login'], '',
                $row['name'], $row['surname'], $row['email'], $row['city'],
                $row['street'], $row['zip']);
    }

    $mysqli->close();
    return $result;
}


/** searchAdmins
 * 
 *   Returns a list of admins (instances of the Admin class) 
 * that fit given criteria. If  $id=="", all admins are returned.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param int $id
 * @return array(Admin)
 */
function searchAdmins($id = "") {
    $mysqli = connectDB();
    $result = array();

    if (empty($id)) {
        if (!$stmt = $mysqli->prepare("SELECT * FROM admin")) {
            throw new DBException('Wrong DB query');
        }
    }
    else{ 
        if (!$stmt = $mysqli->prepare("SELECT * FROM admin
                WHERE admin_id=?")) {
            throw new DBException('Wrong DB query');
        }
        
        $stmt->bind_param("i", $id);
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }

    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch()) {
        $result[] = new Admin($row['admin_id'], $row['login'], '', $row['name'],
                $row['surname'], $row['email']);
    }

    $mysqli->close();
    return $result;
}


/** searchInvoices
 * 
 *   Returns a list of invoices (instances of Invoice) that 
 * fit given criteria. If $id=="", all invoices are returned.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param int $id
 * @param bool $unpaidOnly Returns only unpaid invoices when TRUE.
 * @return array(Invoice)
 */
function searchInvoices($id = "", $unpaidOnly = false) {
    $mysqli = connectDB();
    $result = array();

    $query = "SELECT * FROM invoice";
    if (!empty($id)) {
            $query .= " WHERE invoice_id=?";
        if ($unpaidOnly)
            $query .= " AND ISNULL(date_paid)";
    }
    elseif ($unpaidOnly)
        $query .= " WHERE ISNULL(date_paid)";

    if (!$stmt = $mysqli->prepare($query)) {
        throw new DBException('Wrong DB query');
    }
    
    if (!empty($id))
        $stmt->bind_param("i", $id);
    
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }

    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch()) {
        $result[] = new Invoice($row['invoice_id'], $row['price'],
                $row['date_created'], $row['date_due'], $row['date_paid'],
                $row['customer_id']);
    }

    $mysqli->close();
    return $result;
}


/** searchPublications
 * 
 *   Returns a list of publications (instances of Publication) that 
 * fit given criteria. If all criteria are "", all publications are
 * returned.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param int $id
 * @param string $title
 * @param string $description
 * @param int $price
 * @return array(Publication)
 */
function searchPublications($id = "", $title = "", $description = "",
        $price = "") {
    $mysqli = connectDB();
    $result = array();

    $query = 'SELECT * FROM publication'; 
    if (empty($id) && empty($title) && empty($description) && empty($price)) {
        if (!$stmt = $mysqli->prepare($query)) {
            throw new DBException('Wrong DB query');
        }
    }
    elseif (!empty($id)) {
        $query .= ' WHERE ';
        if (!$stmt = $mysqli->prepare($query."publication_id=?")) {
            throw new DBException('Wrong DB query');
        }
        $stmt->bind_param("i", $id);
    }
    else {
        $query .= ' WHERE ';
        $bindParam = new BindParam(); 
        $qArray = array(); 

        if (!empty($title)) { 
            $qArray[] = 'title REGEXP ?'; 
            $title = sqlPrepare($title);
            $bindParam->add('s', $title); 
        }
        if (!empty($description)) { 
            $qArray[] = 'description REGEXP ?'; 
            $description = sqlPrepare($description);
            $bindParam->add('s', $description); 
        }
        if (!empty($price)) { 
            $qArray[] = 'price = ?'; 
            $bindParam->add('i', $price); 
        }
          
        $query .= implode(' AND ', $qArray); 

        if (!$stmt = $mysqli->prepare($query)) {
            throw new DBException('Wrong DB query');
        }
        call_user_func_array(array($stmt, 'bind_param'), $bindParam->get()); 
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');


    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch()) {
        $result[] = new Publication($row['publication_id'], $row['title'],
                $row['description'], $row['price'], $row['delivery_date'],
                $row['next_delivery']);
    }

    $mysqli->close();
    return $result;
}


/**
 * 
 *   Returns a list of subscriptions (instances of Subscription) that 
 * fit given criteria. If all criteria are "", all subscriptions are
 * returned.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param int $id
 * @param int $customerID
 * @param int $publicationID
 * @return array(Subscription)
 */
function searchSubscriptions($id = "", $customerID = "", $publicationID = "") {
    $mysqli = connectDB();
    $result = array();

    $query = 'SELECT * FROM subscription'; 
    if (empty($id) && empty($customerID) && empty($publicationID)) {
        if (!$stmt = $mysqli->prepare($query)) {
            throw new DBException('Wrong DB query');
        }
    }
    elseif (!empty($id)) {
        $query .= ' WHERE ';
        if (!$stmt = $mysqli->prepare($query."subscription_id=?")) {
            throw new DBException('Wrong DB query');
        }
        $stmt->bind_param("i", $id);
    }
    else {
        $query .= ' WHERE ';
        $bindParam = new BindParam(); 
        $qArray = array(); 

        if (!empty($customerID)) { 
            $qArray[] = 'customer_id=?'; 
            $bindParam->add('i', $customerID); 
        }
        if (!empty($publicationID)) { 
            $qArray[] = 'publication_id=?'; 
            $bindParam->add('i', $publicationID); 
        }
          
        $query .= implode(' AND ', $qArray); 

        if (!$stmt = $mysqli->prepare($query)) {
            throw new DBException('Wrong DB query');
        }
        call_user_func_array(array($stmt, 'bind_param'), $bindParam->get()); 
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');


    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch()) {
        $result[] = new Subscription($row['subscription_id'],
                $row['publication_id'], $row['customer_id']);
    }

    $mysqli->close();
    return $result;
}


/** updateCustomer
 * 
 *   Updates given customer entry according his ID. The argument is
 * an instance of the Customer class that contains the updated
 * values.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.

 * @param Customer $customer
 * @return bool
 */
function updateCustomer($customer) {
    
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("UPDATE customer SET name=?, surname=?,
            street=?, city=?, zip=?, email=?, postman_id=?
            WHERE customer_id=?")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ssssisii", $customer->name, $customer->surname,
            $customer->street, $customer->city, $customer->ZIP,
            $customer->email, $customer->postmanID, $customer->id);
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** updatePostman
 * 
 *   Updates given postman entry according his ID. The argument is
 * an instance of the Postman class that contains the updated values.
 * Throws DBException when query cannot be prepared,
 * DBExecuteException when query cannot be executed and
 * DBLoginException when given login is already registered.
 *
 * @param Postman $postman
 * @return bool
 */
function updatePostman($postman) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("SELECT login FROM postman WHERE login=?
            AND postman_id!=? UNION SELECT login FROM admin WHERE login=?")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("sis", $postman->login, $postman->id, $postman->login);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $stmt->store_result();
        
    if ($stmt->num_rows > 0)
        throw new DBLoginException('This login is already registered');
    $stmt->close();

    if (empty($postman->password)) {
        if (!$stmt = $mysqli->prepare("UPDATE postman SET login=?, name=?,
                surname=?, street=?, city=?, zip=?, email=?
                WHERE postman_id=?")) {
            throw new DBException('Wrong DB query');
        }
    
        $stmt->bind_param("sssssisi", $postman->login, $postman->name,
                $postman->surname, $postman->street, $postman->city,
                $postman->ZIP, $postman->email, $postman->id);
    }
    else{
        if (!$stmt = $mysqli->prepare("UPDATE postman SET login=?,
                password=md5(?), name=?, surname=?, street=?, city=?, zip=?,
                email=? WHERE postman_id=?")) {
            throw new DBException('Wrong DB query');
        }
    
        $stmt->bind_param("ssssssisi", $postman->login, $postman->password,
            $postman->name, $postman->surname, $postman->street, $postman->city,
            $postman->ZIP, $postman->email, $postman->id);
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** updateAdmin
 * 
 *   Updates given admin entry according his ID. The argument is
 * an instance of the Admin class that contains the updated values.
 * Throws DBException when query cannot be prepared,
 * DBExecuteException when query cannot be executed and
 * DBLoginException when given login is already registered.
 *
 * @param Admin $Admin
 * @return bool
 */
function updateAdmin($admin) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("SELECT login FROM postman WHERE login=?
            UNION SELECT login FROM admin WHERE login=? AND admin_id!=?")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ssi", $admin->login, $admin->login, $admin->id);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $stmt->store_result();
        
    if ($stmt->num_rows > 0)
        throw new DBLoginException('This login is already registered');
    $stmt->close();

    if (empty($admin->password)) {
        if (!$stmt = $mysqli->prepare("UPDATE admin SET login=?, name=?,
                surname=?, email=? WHERE admin_id=?")) {
            throw new DBException('Wrong DB query');
        }
    
        $stmt->bind_param("ssssi", $admin->login, $admin->name, $admin->surname,
                $admin->email, $admin->id);
    }
    else {
        if (!$stmt = $mysqli->prepare("UPDATE admin SET login=?,
                password=md5(?), name=?, surname=?, email=?
                WHERE admin_id=?")) {
            throw new DBException('Wrong DB query');
        }
        
        $stmt->bind_param("sssssi", $admin->login, $admin->password,
                $admin->name, $admin->surname, $admin->email, $admin->id);
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** updatePublication
 * 
 *   Updates given publication entry. The argument is an instance of
 * the Publication class that contains the updated values.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param Publication $publication
 * @return bool
 */
function updatePublication($publication) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("UPDATE publication SET title=?,
            description=?, price=?, delivery_date=?, next_delivery=?
            WHERE publication_id=?")) {
        throw new DBException('Wrong DB query');
    }

    $stmt->bind_param("ssisii", $publication->title, $publication->description,
            $publication->price, $publication->delivDate,
            $publication->nextDeliv, $publication->id);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}
  

/** paymentInvoice  
 *
 *   Records a payment for given invoice.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param int $invoiceID
 * @param string $date Payment date in format (YYYY-MM-DD).
 * @return bool
 */
function paymentInvoice($invoiceID, $date) {
    $mysqli = connectDB();
 
    if (!$stmt = $mysqli->prepare("UPDATE invoice SET date_paid=? WHERE invoice_id=?")) {
        throw new DBException('Wrong DB query');
    }

    $stmt->bind_param("si", $date, $invoiceID);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();    
    return TRUE;
}


/** createCustomer
 * 
 *   Creates a DB entry for a new customer.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @param Customer $customer Instance of include/Customer
 * @return bool
 */
function createCustomer($customer) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("INSERT INTO customer(name, surname, street,
            city, zip, email, postman_id) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ssssisi", $customer->name, $customer->surname,
            $customer->street, $customer->city, $customer->ZIP,
            $customer->email, $customer->postmanID);
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** createPostman
 *
 *   Creates a DB entry for a new postman.
 * Throws DBException when query cannot be prepared,
 * DBExecuteException when query cannot be executed and
 * DBLoginException when given login is already registered.
 *
 * @param Postman $postman Instance of include/Postman
 * @return bool
 */
function createPostman($postman) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("SELECT login FROM postman WHERE login=?
            UNION SELECT login FROM admin WHERE login=?")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ss", $postman->login, $postman->login);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $stmt->store_result();
        
    if ($stmt->num_rows > 0)
        throw new DBLoginException('This login is already registered');
    $stmt->close();

    if (!$stmt = $mysqli->prepare("INSERT INTO postman(login, password, name,
            surname, street, city, zip, email)
            VALUES (?, md5(?), ?, ?, ?, ?, ?, ?)")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ssssssis", $postman->login, $postman->password,
            $postman->name, $postman->surname, $postman->street, $postman->city,
            $postman->ZIP, $postman->email);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** createAdmin
 *
 *   Creates a DB entry for a new admin.
 * Throws DBException when query cannot be prepared,
 * Throws DBExecuteException when query cannot be executed and
 * DBLoginException when given login is already registered.
 *
 * @param Admin $admin Instance of include/Admin
 * @return bool
 */
function createAdmin($admin) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("SELECT login FROM postman WHERE login=?
            UNION SELECT login FROM admin WHERE login=?")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ss", $admin->login, $admin->login);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $stmt->store_result();
        
    if ($stmt->num_rows > 0)
        throw new DBLoginException('This login is already registered');
    $stmt->close();

    if (!$stmt = $mysqli->prepare("INSERT INTO admin(login, password, name,
            surname, email) VALUES (?, md5(?), ?, ?, ?)")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("sssss", $admin->login, $admin->password,
            $admin->name, $admin->surname, $admin->email);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** createPublication
 *
 *   Creates a DB entry for a new publication.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @param Publication $publication Instance of include/Publication
 * @return bool
 */
function createPublication($publication) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("INSERT INTO publication(title, description,
            price, delivery_date, next_delivery) VALUES (?, ?, ?, ?, ?)")) {
        throw new DBException('Wrong DB query');
    }

    $stmt->bind_param("ssisi", $publication->title, $publication->description,
            $publication->price, $publication->delivDate,
            $publication->nextDeliv);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** createSubscription
 *
 *   Creates a new DB entry for a new subscription.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @param Subscription $subscription Instance of include/Subscription
 * @return bool
 */
function createSubscription($subscription) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("INSERT INTO subscription(publication_id,
    customer_id) VALUES (?, ?)")) {
        throw new DBException('Wrong DB query');
    }
    $stmt->bind_param("ii", $subscription->pubID, $subscription->cusID);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}

/** createInvoices
 *
 *   Creates invoices for the current month and adds them to the DB.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.

 * @return bool
 */
function createInvoices() {
    $mysqli = connectDB();
    $result = array();

    if (!$stmt = $mysqli->prepare("SELECT customer_id, SUM(price) total_price
            FROM subscription S JOIN publication P ON
            S.publication_id=P.publication_id GROUP BY customer_id")) {
        throw new DBException('Wrong DB query');
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $params); 

    while ($stmt->fetch())
        $result[$row['customer_id']] = $row['total_price'];

    $stmt->close();
    
    foreach ($result as $customerID => $totalPrice) {
        if (!$stmt = $mysqli->prepare("INSERT INTO invoice(price, date_created,
                date_due, customer_id) VALUES (?, CURDATE(),
                DATE_ADD(CURDATE(), INTERVAL +15 DAY), ?)")) {
            throw new DBException('Wrong DB query');
        }
        $stmt->bind_param("ii", $totalPrice, $customerID);

        if (!$stmt->execute())
            throw new DBExecuteException('Operation cannot be executed');
    
        $stmt->close();
    }

    $mysqli->close();
    return TRUE;
}


/** deletePostman
 *
 *   Deletes given postman from the DB. If customers are registered
 * to the postman, they are re-registered to the postman with
 * id=$altPostmanID. Function uses SQL procedure
 * delete_postman($postmanID, $altPostmanID).
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.

 * @param int $postmanID Postman to be deleted.
 * @param int $altPostmanID Postman for customers to be re-registered to.
 */
function deletePostman($postmanID, $altPostmanID) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("CALL delete_postman(?, ?)")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("ii", $postmanID, $altPostmanID);
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** deleteAdmin
 *
 *   Deletes given admin from the DB.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.

 * @param int $adminID Admin to be deleted.
 */
function deleteAdmin($adminID) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("DELETE FROM admin
            WHERE admin_id=?")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("i", $adminID);
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** deletePublication
 *
 *   Deletes given publication from the DB. Uses trigger
 * deleted_publication that deletes all subscriptions with this
 * publication.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @param int $publicationID
 * @return bool
 */
function deletePublication($publicationID) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("DELETE FROM publication
            WHERE publication_id=?")) {
        throw new DBException('Wrong DB query');
    }
    
    $stmt->bind_param("i", $publicationID);
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** deleteSubscription
 *
 *   Deletes given subscription from the DB.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @param int $subscriptionID
 * @return bool
 */
function deleteSubscription($subscriptionID) {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("DELETE FROM subscription WHERE
            subscription_id=?")) {
        throw new DBException('Wrong DB query');
    }
    $stmt->bind_param("i", $subscriptionID);

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** deleteAllInvoices
 *
 * Deletes all invoices from the DB.For project testing ONLY.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @return bool
 */
function deleteAllInvoices(){
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("DELETE FROM invoice")) {
        throw new DBException('Wrong DB query');
    }

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $mysqli->close();
    return TRUE;
}


/** deleteInvoice
 *
 * Deletes given invoice from the DB in case it was not paid yet.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @param int $id
 * @return bool
 */
function deleteInvoice($id){
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("DELETE FROM invoice WHERE
            invoice_id=? AND ISNULL(date_paid)")) {
        throw new DBException('Wrong DB query');
    }
    $stmt->bind_param("i", $id);

    if (!$stmt->execute() || $stmt->affected_rows == 0)
        throw new DBExecuteException('Operation cannot be executed');
    $mysqli->close();
    return TRUE;
}


/** getDeliveriesForDay
 * 
 *   Returns today's deliveries for a given postman according to his
 * login.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 * 
 * @param string $login Postman's login
 * @return array() array[city][zip][street][surname name][title][]
 */
function getDeliveriesForDay($login) {
    $mysqli = connectDB();
    $result = array();

    if (!$stmt = $mysqli->prepare("SELECT C.city, C.zip, C.street, C.surname,
            C.name, P.title FROM customer C JOIN
            subscription S ON C.customer_id=S.customer_id JOIN publication P ON
            S.publication_id=P.publication_id JOIN postman M ON
            C.postman_id=M.postman_id WHERE M.login=? AND
            P.delivery_date=CURDATE() AND
            ((ISNULL(C.inactive_since) AND ISNULL(C.inactive_till)) OR
            NOT (P.delivery_date>=C.inactive_since AND
            P.delivery_date<=C.inactive_till))")) {
        throw new DBException('Wrong DB query');
    }
   
    $stmt->bind_param("s", $login);
    

    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');

    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }

    call_user_func_array(array($stmt, 'bind_result'), $params); 
    
    while ($stmt->fetch()) {
        $result[$row['city']][$row['zip']][$row['street']][$row['surname'].", "
                .$row['name']][]= $row['title']; 
    }

    $mysqli->close();
    return $result;
}


/** updateDeliveryDates
 *  
 *   Updates delivery_date of each publication regarding
 * next_delivery column. Needs to be run every day after midnight.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @return bool
 */
function updateDeliveryDates() {
    $mysqli = connectDB();

    if (!$stmt = $mysqli->prepare("UPDATE publication SET
            delivery_date=DATE_ADD(delivery_date, INTERVAL +next_delivery DAY)
            WHERE delivery_date<CURDATE()")) {
        throw new DBException('Wrong DB query');
    }

    do {
        if (!$stmt->execute())
            throw new DBExecuteException('Operation cannot be executed');

        $result = $stmt->affected_rows;
    } while ($result);

    $mysqli->close();    
    return TRUE;
}


/** truncateAllTables
 *
 *   Truncates all DB tables and inserts default values.
 * Throws DBException when query cannot be prepared and
 * DBExecuteException when query cannot be executed.
 *
 * @return bool
 */
function truncateAllTables() {
    $mysqli = connectDB();

    $truncateArray = array("subscription", "publication", "customer", "postman",
            "invoice", "admin");

    if (!$stmt = $mysqli->prepare("SET FOREIGN_KEY_CHECKS = 0")) {
        throw new DBException('Wrong DB query');
    }
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');
    $stmt->close();

    foreach ($truncateArray as $table){
        if (!$stmt = $mysqli->prepare("TRUNCATE ".$table)) {
            throw new DBException('Wrong DB query');
        }
        if (!$stmt->execute())
            throw new DBExecuteException('Operation cannot be executed');
        $stmt->close();
    }
 
    if (!$stmt = $mysqli->prepare("SET FOREIGN_KEY_CHECKS = 1")) {
        throw new DBException('Wrong DB query');
    }
    if (!$stmt->execute())
        throw new DBExecuteException('Operation cannot be executed');
    $stmt->close();
   
    $insertArray = array("INSERT INTO admin(login, password, name, surname, email) VALUES ('admin',md5('12345'),'Big','Boss','big@boss.com')",
            "INSERT INTO postman(login, password, name, surname, street, city, zip, email)
            VALUES ('postman', md5('abcde'), 'Franti¹ek', 'Doruèovatel', 'U Mostku 12', 'Brno', '62100', 'frantisek@dorucovatel.cz')",
            "INSERT INTO postman(login, password, name, surname, street, city, zip, email)
            VALUES ('bedrich', md5('neheslo'), 'Bedøich', 'Nedoruèovatel', 'U sokolovny 42', 'Brno', '63500', 'bedrich@nedorucovatel.cz')",
            "INSERT INTO postman(login, password, name, surname) VALUES ('Romèa', md5('Heslo'), 'Roman', 'Tyèka')",
            "INSERT INTO customer(name, surname, street,
            city, zip, email, postman_id) VALUES ('Prokop', 'Dveøe', 'Pøístavní 15', 'Brno-Bystrc', '63500', 'prokop@dvere.com', 3)",
            "INSERT INTO customer(name, surname, street,
            city, zip, email, postman_id) VALUES ('Tomá¹', 'Jedno', 'Vìtrná 549', 'Brno-Bystrc', '63500', 'tomas@jedno.com', 3)",
            "INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Petra', 'Kiwiová', 'Odbojáøská 69', 'Brno-Bystrc', '63500', 2)",
            "INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Jana', 'Nová', 'Bo¾etìchova 20', 'Brno-Královo Pole', '61200', 1)",
            "INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Zlata', 'Støíbrná', 'Metodìjova 42', 'Brno-Královo Pole', '61200', 1)",
            "INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Zlata', 'Støíbrná', 'Chaloupkova 128', 'Brno-Královo Pole', '61200', 1)",
            "INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Michaela', 'Zdravá', 'Slovanské námìstí 256', 'Brno-Královo Pole', '63500', 1)",

            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Maxim', 'Èasopis pro divoèáky', 159, CURDATE(), 7)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Mateøídou¹ka', 'Pro mlad¹í ètenáøe.', 99, CURDATE(), 5)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Bravo', 'Pro princezcny.', 39, CURDATE(), 14)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('MF Dnes', 'Denní tisk.', '399', CURDATE(), 1)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('21. stoleti', '', '139', CURDATE(), 28)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Sport', '', '299', CURDATE(), 1)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Køí¾ovky', '', '149', CURDATE(), 2)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Kulturní novinky', '', '599', CURDATE(), 3)",
            "INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Bulvár', '', '9', CURDATE(), 1)",

            "INSERT INTO subscription(publication_id, customer_id) VALUES (1, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (2, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (3, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (4, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (5, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (6, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (7, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (8, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (9, 1)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (3, 2)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (4, 2)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (5, 2)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (6, 2)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (2, 3)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (6, 3)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (9, 3)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (1, 4)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (3, 4)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (4, 4)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (8, 4)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (4, 5)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (5, 5)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (6, 5)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (7, 5)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (9, 5)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (4, 6)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (7, 6)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (1, 7)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (2, 7)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (4, 7)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (6, 7)",
            "INSERT INTO subscription(publication_id, customer_id) VALUES (7, 7)");

    foreach ($insertArray as $row) {
        if (!$stmt = $mysqli->prepare($row)) {
            throw new DBException('Wrong DB query');
        }
        if (!$stmt->execute())
            throw new DBExecuteException('Operation cannot be executed');
        $stmt->close();
    }

    $mysqli->close();    
    return TRUE;
}
