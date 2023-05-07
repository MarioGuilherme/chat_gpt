<?php

    declare(strict_types=1);

    namespace Mario\ApiGatewayChatGpt\Core;

    use Exception;
    use PDO;
    use PDOStatement;

    class Database {
        /**
         * Driver do banco de dados.
         * @var string
         */
        private static string $driver;

        /**
         * Host do banco de dados.
         * @var string
         */
        private static string $host;

        /**
         * Nome do banco de dados.
         * @var string
         */
        private static string $database;

        /**
         * Usuário do banco de dados.
         * @var string
         */
        private static string $user;

        /**
         * Senha do banco de dados.
         * @var string
         */
        private static string $password;

        /**
         * Colação de caracteres do banco de dados.
         * @var string
         */
        private static string $charset;

        /**
         * Instancia de conexão com o banco de dados.
         * @var PDO
         */
        private PDO $PDO;

        /**
         * Método responsável por configurar a classe.
         * @param string $driver Driver do banco de dados
         * @param string $host Host do banco de dados
         * @param string $database Nome do banco de dados
         * @param string $user Usuário do banco de dados
         * @param string $password Senha do banco de dados
         * @param string $charset Colação do banco de dados
         * @return void
         */
        public static function config(string $driver, string $host, string $database, string $user, string $password, string $charset) : void {
            self::$driver = $driver;
            self::$host = $host;
            self::$database = $database;
            self::$user = $user;
            self::$password = $password;
            self::$charset = $charset;
        }

        /**
         * Construtor da classe que define a tabela e inicia e conexão com o Banco de dados.
         */
        public function __construct() {
            $this->setConnection();
        }

        /**
         * Método responsável por criar uma conexão com o banco de dados.
         * @return void
         */
        private function setConnection() : void {
            try {
                $this->PDO = new PDO(self::$driver.":host=".self::$host.";dbname=".self::$database.";charset=".self::$charset, self::$user, self::$password);
                $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(Exception $error) {
                throw new Exception($error->getMessage(), $error->getCode());
            }
        }

        /**
         * Método responsável por executar SQL juntamente com parâmetros no banco de dados.
         * @param string $sql SQL a ser executada
         * @param array $params Parâmetros da SQL (array [$value])
         * @return PDOStatement Objeto PDOStatement
         */
        public function execute(string $sql, array $params = []) : PDOStatement {
            try {
                (object) $stmt = $this->PDO->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } catch(Exception $error) {
                throw new Exception($error->getMessage(), $error->getCode());
            }
        }

        /**
         * Método responsável por realizar seleções no banco de dados.
         * @param string $fields Campos da tabela
         * @param string $join Join com outras tabelas
         * @param string $where Condição para o SELECT
         * @param string $limit Limite de linhas
         * @param array $params Parâmetros da SQL (array [$value])
         * @return PDOStatement Objeto PDOStatement
         */
        public function select(string $from, string $fields = "*", string $join = "", string $where = "", string $limit = "", array $params = []) : PDOStatement {
            (string) $where = strlen($where) ? "WHERE $where" : "";
            (string) $limit = strlen($limit) ? "LIMIT $limit" : "";
            (string) $sql = "SELECT $fields FROM $from $join $where $limit";
            return $this->execute($sql, $params);
        }

        /**
         * Método responsável por realizar inserções no dados no banco.
         * @param array $values Valores a serem inseridos (array associativo ["field" => $value])
         * @return int ID inserido
         */
        public function insert(string $into, array $values) : int {
            (array) $fields = array_keys($values);
            (array) $binds  = array_pad([], count($fields), "?");
            (string) $sql = "INSERT INTO $into (".implode(", " , $fields).") VALUES (".implode(", ", $binds).")";
            $this->execute($sql, array_values($values));
            return (int) $this->PDO->lastInsertId();
        }

        /**
         * Método responsável por realizar atualizações no banco de dados.
         * @param string $where Condição para atualização
         * @param array $values Valores a serem atualizados (array associativo ["field" => $value])
         * @return bool True se a atualização for bem sucedida
         */
        public function update(string $table, string $where, array $values) : bool {
            (array) $fields = array_keys($values);
            (string) $sql = "UPDATE $table SET " . implode(" = ?, ", $fields) . " = ? WHERE $where LIMIT 1";
            $this->execute($sql, array_values($values));
            return true;
        }

        /**
         * Método responsável por realizar exclusões no dados do banco.
         * @param string $where Condição para exclusão
         * @param array $params Parâmetros da SQL (array [$value])
         * @return bool True se a exclusão for bem sucedida
         */
        public function delete(string $table, string $where, array $params = []) : bool {
            (string) $sql = "DELETE FROM $table WHERE $where";
            $this->execute($sql, $params);
            return true;
        }
    }