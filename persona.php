<?php
class Persona {
    // Atributos
    private $nombre;
    private $apellido;
    private $fechaNacimiento;
    private $edad;
    private $email;
    private $telefono;
    private $genero;

    // Constructor
    public function __construct($nombre, $apellido, $fechaNacimiento, $email, $telefono, $genero) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->genero = $genero;
        $this->calcularEdad();
    }

    // Método para calcular la edad
    public function calcularEdad() {
        $fechaNac = new DateTime($this->fechaNacimiento);
        $fechaActual = new DateTime();
        $diferencia = $fechaNac->diff($fechaActual);
        $this->edad = $diferencia->y;
    }

    // Getters
    public function getNombre() {
        return $this->nombre;
    }

    public function getApellido() {
        return $this->apellido;
    }

    public function getNombreCompleto() {
        return $this->nombre . " " . $this->apellido;
    }

    public function getFechaNacimiento() {
        return $this->fechaNacimiento;
    }

    public function getEdad() {
        return $this->edad;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getGenero() {
        return $this->genero;
    }

    public function getGeneroTexto() {
        $generos = [
            'M' => 'Masculino',
            'F' => 'Femenino',
            'O' => 'Otro'
        ];
        return isset($generos[$this->genero]) ? $generos[$this->genero] : 'No especificado';
    }

    // Métodos de acciones
    public function comer() {
        return [
            'accion' => 'comer',
            'mensaje' => $this->getNombreCompleto() . ' está comiendo',
            'hora' => date('H:i:s')
        ];
    }

    public function caminar() {
        return [
            'accion' => 'caminar',
            'mensaje' => $this->getNombreCompleto() . ' está caminando',
            'hora' => date('H:i:s')
        ];
    }

    public function hablar() {
        return [
            'accion' => 'hablar',
            'mensaje' => $this->getNombreCompleto() . ' está hablando',
            'hora' => date('H:i:s')
        ];
    }

    public function dormir() {
        return [
            'accion' => 'dormir',
            'mensaje' => $this->getNombreCompleto() . ' está durmiendo',
            'hora' => date('H:i:s')
        ];
    }

    public function estudiar() {
        return [
            'accion' => 'estudiar',
            'mensaje' => $this->getNombreCompleto() . ' está estudiando',
            'hora' => date('H:i:s')
        ];
    }

    // Método para obtener datos como array
    public function toArray() {
        return [
            'nombreCompleto' => $this->getNombreCompleto(),
            'nombre' => $this->getNombre(),
            'apellido' => $this->getApellido(),
            'email' => $this->getEmail(),
            'telefono' => $this->getTelefono(),
            'edad' => $this->getEdad(),
            'fechaNacimiento' => $this->getFechaNacimiento(),
            'genero' => $this->getGenero(),
            'generoTexto' => $this->getGeneroTexto()
        ];
    }

    // Método estático para crear desde array
    public static function createFromArray($data) {
        return new Persona(
            $data['nombre'],
            $data['apellido'],
            $data['fechaNacimiento'],
            $data['email'],
            $data['telefono'],
            $data['genero']
        );
    }
}
?>