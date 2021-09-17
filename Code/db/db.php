<?php

    class Database {
        private $connection;

        private $selectDocumentById;
        private $selectDocumentByCode;
        private $selectDocumentsByDepartmentId;

        private $selectUserByUsername;

        private $selectDepartments;
        private $selectDepartmentEmailById;

        private $selectStatusChanges;
        private $selectStatuses;
        private $selectStatusIdByName;

        private $insertUser;
        private $insertDocument;
        private $insertStatusChange;

        private $updateDocumentAfterInsertion;
        private $updateDocumentViewTimes;

        private $checkIfDocumentExistsByHash;
        private $changeDocumentStatus;

        public function __construct() {
            $config = parse_ini_file('config/config.ini', true);
            $type = $config['db']['type'];
            $host = $config['db']['host'];
            $database = $config['db']['database'];
            $username = $config['db']['username'];
            $password = $config['db']['password'];
            $this->init($type, $host, $database, $username, $password);
        }

        private function init($type, $host, $database, $username, $password) {
            try {
                $this->connection = new PDO("$type:host=$host;dbname=$database", $username, $password,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $this->prepareStatements();
            } 
            catch(PDOException $e) {
                die("Connection to database failed: " . $e->getMessage());
            }
        }

        private function prepareSelectAllDocuments($order, $orderType){
            $this->order = $order;
            $this->orderType = $orderType;

            $sql = "SELECT doc.name, doc.id, doc.uploaded, s.id AS statusId, dep.name AS department, doc.path, s.color, s.status 
                FROM documents doc 
                JOIN departments dep ON doc.department_id = dep.id 
                JOIN statuses s ON doc.status_id = s.id 
                ORDER BY {$this->order} {$this->orderType}";
            
            $this-> selectAllDocuments = $this->connection->prepare($sql);
        }

        private function prepareSelectDocumentsByDepartmentIdQuery($order, $orderType){
            $this->order = $order;
            $this->orderType = $orderType;

            $sql = "SELECT doc.name, doc.id, doc.uploaded, doc.path, s.color, s.status, dep.name AS department
                FROM documents doc 
                JOIN departments dep ON doc.department_id = dep.id 
                JOIN statuses s ON doc.status_id = s.id 
                WHERE dep.id = :id AND s.status != 'Архивиран' ORDER BY {$this->order} {$this->orderType}";
            
            $this-> selectDocumentsByDepartmentId = $this->connection->prepare($sql);
        }

        private function prepareUpdateDocumentViewTimesQuery($isFirstViewing) {
            if ($isFirstViewing) {
                $sql = "UPDATE documents SET firstOpened = :time, lastOpened = :time, timesOpened = :numTimes WHERE id = :id LIMIT 1";
                $this->updateDocumentViewTimes = $this->connection->prepare($sql);
            }
            else {
                $sql = "UPDATE documents SET lastOpened = :time, timesOpened = :numTimes WHERE id = :id LIMIT 1";
                $this->updateDocumentViewTimes = $this->connection->prepare($sql);
            }
        }

        private function prepareStatements() {
            $sql = "INSERT INTO administrators(name, password) VALUES (:name, :password)";
            $this->insertUser = $this->connection->prepare($sql);

            $sql = "SELECT email FROM departments WHERE id = :id LIMIT 1";
            $this->selectDepartmentEmailById = $this->connection->prepare($sql);

            $sql = "SELECT id FROM statuses WHERE status = :status LIMIT 1";
            $this->selectStatusIdByName = $this->connection->prepare($sql);

            $sql = "SELECT id, status, color FROM statuses WHERE status != 'Архивиран' ORDER BY id ASC";
            $this->selectStatuses = $this->connection->prepare($sql);

            $sql = "SELECT d.id, d.name AS name, dep.name AS department, status, color, description, uploaded, timesOpened, firstOpened, lastOpened 
                FROM documents d JOIN statuses s ON d.status_id = s.id 
                JOIN departments dep ON dep.id = d.department_id 
                WHERE d.code = :code LIMIT 1";
            $this->selectDocumentByCode = $this->connection->prepare($sql);

            $sql = "SELECT d.id, d.name AS name, dep.name AS department, path, status, color, description, uploaded, timesOpened, s.id AS statusId
                FROM documents d JOIN statuses s ON d.status_id = s.id 
                JOIN departments dep ON dep.id = d.department_id 
                WHERE d.id = :id LIMIT 1";
            $this->selectDocumentById = $this->connection->prepare($sql);

            $sql = 'SELECT id, username, password, admin FROM (
                SELECT id AS id, name AS username, password AS password, "1" AS admin FROM administrators
                UNION
                SELECT id as id, email AS username, password AS password, "0" AS admin FROM departments
                ) AS mergedUsers WHERE username = :name LIMIT 1';
            $this->selectUserByUsername = $this->connection->prepare($sql);

            $sql = "SELECT COUNT(*) AS count FROM documents WHERE hash = :hash LIMIT 1";
            $this->checkIfDocumentExistsByHash = $this->connection->prepare($sql);

            $sql = "SELECT * FROM departments";
            $this->selectDepartments = $this->connection->prepare($sql);

            $sql = "INSERT INTO documents (name, description, department_id, uploaded) 
                VALUES (:name, :description, :departmentID, :uploaded);";
            $this->insertDocument = $this->connection->prepare($sql);

            $sql = "INSERT INTO statusChanges (status, document_id, role, changed)
                VALUES (:status, :document, :role, :changed);";
            $this->insertStatusChange = $this->connection->prepare($sql);

            $sql = "UPDATE documents SET code = :code, hash = :hash, path = :path WHERE id = :id LIMIT 1";
            $this->updateDocumentAfterInsertion = $this->connection->prepare($sql);

            $sql = "UPDATE documents SET status_id = :status WHERE id = :id LIMIT 1";
            $this->changeDocumentStatus = $this->connection->prepare($sql);

            $sql = "SELECT ch.status AS status, ch.changed AS changed, ch.role AS role, s.status AS statusName, s.color AS color
                FROM statusChanges ch
                LEFT JOIN statuses s ON ch.status = s.id 
                WHERE ch.document_id = :id ORDER BY ch.changed ASC";
            $this->selectStatusChanges = $this->connection->prepare($sql);
        }

        public function selectDepartmentEmailByIdQuery($id) {
            try {
                $success = $this->selectDepartmentEmailById->execute(["id" => $id]);

                if ($success) {
                    $email = $this->selectDepartmentEmailById->fetch()['email'];

                    $result = ["success" => true, "data" => $email];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch(PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectStatusIdByNameQuery($status) {
            try {
                $success = $this->selectStatusIdByName->execute(["status" => $status]);

                if ($success) {
                    $status = $this->selectStatusIdByName->fetch()['id'];

                    $result = ["success" => true, "data" => $status];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch(PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectStatusesQuery() {
            try {
                $success = $this->selectStatuses->execute();

                if ($success) {
                    $statuses = $this->selectStatuses->fetchAll();

                    $result = ["success" => true, "data" => $statuses];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch(PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectStatusChangesQuery($id) {
            try {
                $success = $this->selectStatusChanges->execute(["id" => $id]);

                if ($success) {
                    $statusChanges = $this->selectStatusChanges->fetchAll();

                    $result = ["success" => true, "data" => $statusChanges];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch(PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function updateDocumentAfterInsertionQuery($id, $code, $hash, $path) {
            try {
                $this->updateDocumentAfterInsertion->execute(["code" => $code, "hash" => $hash, 
                    "path" => $path, "id" => $id]);

                return ["success" => true];
            } 
            catch (PDOException $e) {
                $this->connection->rollBack();
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function updateDocumentViewTimesQuery($id, $numTimes) {
            try {
                $isFirstViewing = ((int)$numTimes == 0);
                $this->prepareUpdateDocumentViewTimesQuery($isFirstViewing);

                $this->updateDocumentViewTimes->execute(["id" => $id, "time" => time(), "numTimes" => ++$numTimes]);
                
                return ["success" => true];
            } 
            catch (PDOException $e) {
                $this->connection->rollBack();
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function changeDocumentStatusQuery($id, $status) {
            try {
                $this->changeDocumentStatus->execute(["id" => $id, "status" => $status]);

                return ["success" => true];
            } 
            catch (PDOException $e) {
                $this->connection->rollBack();
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function insertDocumentQuery($name, $description, $department) {
            try {
                $this->insertDocument->execute(["name" => $name, "description" => $description, 
                    "departmentID" => $department, "uploaded" => time()]);

                return ["success" => true, "data" => $this->connection->lastInsertId()];
            } 
            catch (PDOException $e) {
                $this->connection->rollBack();
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function insertStatusChangeQuery($document_id, $status, $role) {
            try {
                $this->insertStatusChange->execute(["status" => $status, "document" => $document_id, 
                    "role" => $role, "changed" => time()]);

                return ["success" => true];
            } 
            catch (PDOException $e) {
                $this->connection->rollBack();
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectDepartmentsQuery(){
            try {
                $success = $this->selectDepartments->execute();

                if($success){
                    $departments = $this->selectDepartments->fetchAll();

                    $result = ["success" => true, "data" => $departments];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch(PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function checkIfDocumentExistsByHashQuery($hash){
            try {
                $success = $this->checkIfDocumentExistsByHash->execute(["hash" => $hash]);

                if ($success) {
                    $count = $this->checkIfDocumentExistsByHash->fetch()['count'];

                    $data = false;
                    if ($count > 0) {
                        $data = true;
                    }

                    $result = ["success" => true, "exists" => $data];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch(PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectDocumentByCodeQuery($code){
            try {
                $success = $this->selectDocumentByCode->execute(["code" => $code]);

                if ($success) {
                    $documents = $this->selectDocumentByCode->fetchAll();

                    if (count($documents) == 0) {
                        $result = ["success" => true, "exists" => false];
                    }

                    else {
                        $result = ["success" => true, "exists" => true, "data" => $documents[0]];
                    }
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch (PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectDocumentByIdQuery($id){
            try {
                $success = $this->selectDocumentById->execute(["id" => $id]);

                if ($success) {
                    $documents = $this->selectDocumentById->fetchAll();

                    if (count($documents) == 0) {
                        $result = ["success" => true, "exists" => false];
                    }
                    else {
                        $result = ["success" => true, "exists" => true, "data" => $documents[0]];
                    }
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch (PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function insertUserQuery($name, $password) {
            try {
                $this->insertUser->execute(["name" => $name, "password" => password_hash($password)]);

                return ["success" => true];
            } 
            catch (PDOException $e) {
                $this->connection->rollBack();
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectDocumentsByDepartmentIdQuery($id, $order, $orderType){
            try {
                $this->prepareSelectDocumentsByDepartmentIdQuery($order, $orderType);
                $success = $this->selectDocumentsByDepartmentId->execute(["id" => $id]);
                if ($success) {
                    $documents = $this->selectDocumentsByDepartmentId->fetchAll();
                    $result = ["success" => true, "data" => $documents];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            }
            catch (PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectAllDocumentsQuery($order, $orderType){
            try {
                $this->prepareSelectAllDocuments($order, $orderType);

                $success = $this->selectAllDocuments->execute();

                if ($success) {
                    $documents = $this->selectAllDocuments->fetchAll();

                    $result = ["success" => true, "data" => $documents];
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            }
            catch (PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        public function selectUserByUsernameQuery($name){
            try {
                $success = $this->selectUserByUsername->execute(["name" => $name]);

                if ($success) {
                    $users = $this->selectUserByUsername->fetchAll();

                    if (count($users) == 0) {
                        $result = ["success" => true, "exists" => false];
                    }
                    else {
                        $result = ["success" => true, "exists" => true, "data" => $users[0]];
                    }
                }
                else {
                    $result = ["success" => false, "error" => "Query failed: "];
                }
                return $result;
            } 
            catch (PDOException $e) {
                return ["success" => false, "error" => "Connection failed: " . $e->getMessage()];
            }
        }

        /**
         * Close the connection to the DB
         */
        function __destruct() {
            $this->connection = null;
        }
    }
?>