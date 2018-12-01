<?php

  class connectDB {

    /**
     * Clase para conexion a base de datos por medio del metodo PDO
     *
     * @param string $database base de datos (mysql,pgsql)
     * @param string $host direccion del host de la base de datos
     * @param string $dbname Nombre de la base de datos
     * @param string $user Nombre de usuario para la conexcion
     * @param string $password Contrasenya del usuario
     *
     */

    protected $database = 'pgsql';
    protected $host = '127.0.0.1';
    protected $dbname = 'test';
    protected $user = 'postgres';
    protected $password = '1234';
    public $conn;

    public function __construct(){
      try {
        $this->conn = new PDO($this->database.":host=".$this->host." dbname=".$this->dbname." user=".$this->user." password=".$this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $this->conn;
      }
      catch (PDOException $exepcion) {
          return $exepcion;
      }
    }

    /**
     * Ejecuta y devuelve el número de filas afectadas de una consulta UPDATE / INSERT / DELETE.
     *
     * @param string $consulta Consulta en formato PDO
     * @param array $valores Valores vinculados a la consulta
     * @return int Número de filas afectadas después de ejecutar la consulta
     */
    function getFilasAfectadas($consulta, $valores) {
        try{
            $consultaSQL = $this->conn->prepare($consulta);
            $consultaSQL -> execute($valores);
            return $consultaSQL -> rowCount();
        }  catch (Exception $e){
            return false;
        }
    }

    /**
     * Ejecuta y devuelve el resultado de una consulta SELECT en formato JSON.
     *
     * @param string $consulta Consulta en formato PDO
     * @param array $valores Valores vinculados a la consulta
     * @return string Resultado de la consulta (en JSON)
     */
    function getFilasJSON($consulta, $valores) {
        return json_encode($this->getFilasArrayUTF8($consulta, $valores));
    }

    /**
     * Devuelve un arreglo con todas sus llaves en letras minúscula.
     * @param array $array Arreglo del que se cambiarán las llaves
     * @return array NUevo arreglo con las llaves en minúscula
     */
    function arregloMinusculas($array){
        $rowsn = array();
        for($i=0;$i<count($array);$i++){
            $rowsn[$i]=array();
            foreach ($array[$i] as $k => $v) {
               $rowsn[$i][mb_strtolower($k)] = $v;
            }
        }
        return $rowsn;
    }

    /**
     * Ejecuta y devuelve el resultado de una consulta SELECT en formato JSON.
     * Es igual a la función getFilasJSON($consulta, $vars, $conexion), pero
     * adicionalmente cambia la codificación de los valores a UTF8
     *
     * @param string $consulta Consulta en formato PDO
     * @param array $valores Valores vinculados a la consulta
     * @return string Resultado de la consulta (en JSON)
     */
    function getFilasArrayUTF8($consulta, $valores) {
        try{
            $pdo = $this->conn->prepare($consulta);
            $pdo-> execute($valores);
            $res = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $this->arregloMinusculas($res);
        }
        catch (Exception $ex){
            return NULL;
        }
    }

    /**
     * Ejecuta y devuelve el resultado de una consulta SELECT en formato JSON.
     * Es igual a la función getFilasJSON($consulta, $vars, $conexion), pero
     * adicionalmente cambia la codificación de los valores a UTF8
     *
     * @param string $consulta Consulta en formato PDO
     * @param array $valores Valores vinculados a la consulta
     * @return string Resultado de la consulta (en JSON)
     */
    function getFilasJSONutf8($consulta, $valores) {

        $consultaSQL = $this->conn->prepare($consulta);
        if ($consultaSQL->execute($valores)) {
            $resultado = $this->filasLATIN1aUTF8($consultaSQL);
            return json_encode( $this->arregloMinusculas($resultado));
        }
        return NULL;
    }

    /**
     * Ejecuta y devuelve el resultado de una consulta SELECT en un array.
     *
     * @param string $consulta Consulta en formato PDO
     * @param array $valores Valores vinculados a la consulta
     * @return string Resultado de la consulta (en array)
     */
    function getFilasUTF8($consulta, $valores) {

        $consultaSQL = $this->conn->prepare($consulta);
        if ($consultaSQL->execute($valores)) {
            $resultado = $this->arregloMinusculas($this->filasLATIN1aUTF8($consultaSQL));
            return $resultado;
        }
        return NULL;
    }

    /**
     * Convierte de codificación latin1 a utf8, los valores de todas las columnas
     *  de todos las filas de la consulta
     *
     * @param PDOStatement $consultaSQL Contiene los resultados de la consulta
     * @return array Arreglo con las filas de $consultaSQL codificadas en utf8
     */
    function filasLATIN1aUTF8($consultaSQL) {

        $resultado = array();
        while ($fila = $consultaSQL->fetch(PDO::FETCH_ASSOC)) {
            foreach ($fila as $columna => $valor) {
                $str_tmp = utf8_encode($fila[$columna]);
                $fila[$columna] = $str_tmp;
            }
            $resultado[] = $fila;
        }
        return $resultado;
    }

  }

 ?>
