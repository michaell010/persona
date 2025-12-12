<?php
// Iniciar sesión para persistir datos
session_start();

// Incluir la clase Persona
require_once 'Persona.php';

// Archivo para guardar los datos
$archivoDatos = 'personas.json';

// Función para cargar personas desde el archivo JSON
function cargarPersonasDesdeArchivo($archivo) {
    if (file_exists($archivo)) {
        $datos = json_decode(file_get_contents($archivo), true);
        $personas = [];
        foreach ($datos as $data) {
            $personas[] = new Persona(
                $data['nombre'],
                $data['apellido'],
                $data['fechaNacimiento'],
                $data['email'],
                $data['telefono'],
                $data['genero']
            );
        }
        return $personas;
    }
    return [];
}

// Función para guardar personas en el archivo JSON
function guardarPersonasEnArchivo($personas, $archivo) {
    $datos = [];
    foreach ($personas as $persona) {
        $datos[] = [
            'nombre' => $persona->getNombre(),
            'apellido' => $persona->getApellido(),
            'fechaNacimiento' => $persona->getFechaNacimiento(),
            'email' => $persona->getEmail(),
            'telefono' => $persona->getTelefono(),
            'genero' => $persona->getGenero()
        ];
    }
    file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT));
}

// Función para inicializar datos por defecto
function inicializarDatosPorDefecto($archivo) {
    $personasIniciales = [
        ['Michaell', 'Gomez', '2007-04-01', 'Michaell01gomez63@gmail.com', '3208104890', 'M'],
        ['Karen', 'Gomez', '1982-06-25', 'Karen1gomez63@gmail.com', '3142715792', 'F'],
        ['Stiven', 'Rodriguez', '2006-10-31', 'StivenRZ@gmail.com', '3178546648', 'M']
    ];
    
    $personas = [];
    foreach ($personasIniciales as $data) {
        $personas[] = new Persona($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
    }
    
    guardarPersonasEnArchivo($personas, $archivo);
    return $personas;
}

// Cargar personas
if (!file_exists($archivoDatos)) {
    $personas = inicializarDatosPorDefecto($archivoDatos);
} else {
    $personas = cargarPersonasDesdeArchivo($archivoDatos);
}

// Manejar eliminaciones
if (isset($_GET['eliminar'])) {
    if ($_GET['eliminar'] === 'todos') {
        $personas = [];
        // Si eliminan todos, volvemos a inicializar con datos por defecto
        $personas = inicializarDatosPorDefecto($archivoDatos);
    } else {
        $index = intval($_GET['eliminar']);
        if (isset($personas[$index])) {
            array_splice($personas, $index, 1);
        }
    }
    
    guardarPersonasEnArchivo($personas, $archivoDatos);
    header('Location: index.php');
    exit();
}

// Procesar formulario para crear nueva persona
$mensajeExito = '';
if ($_POST && isset($_POST['nombre']) && !empty($_POST['nombre'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $genero = $_POST['genero'];

    // Validar datos
    if ($nombre && $apellido && $fechaNacimiento && $email && $telefono && $genero) {
        $nuevaPersona = new Persona($nombre, $apellido, $fechaNacimiento, $email, $telefono, $genero);
        $personas[] = $nuevaPersona;
        guardarPersonasEnArchivo($personas, $archivoDatos);
        
        $mensajeExito = " Persona <strong>'{$nuevaPersona->getNombreCompleto()}'</strong> creada exitosamente!";
        
        // Limpiar formulario
        $_POST = [];
    }
}

// Funciones auxiliares para estadísticas
function contarHombres($personas) {
    $count = 0;
    foreach ($personas as $persona) {
        if ($persona->getGenero() === 'M') {
            $count++;
        }
    }
    return $count;
}

function contarMujeres($personas) {
    $count = 0;
    foreach ($personas as $persona) {
        if ($persona->getGenero() === 'F') {
            $count++;
        }
    }
    return $count;
}

function calcularEdadPromedio($personas) {
    if (count($personas) === 0) return 0;
    
    $totalEdad = 0;
    foreach ($personas as $persona) {
        $totalEdad += $persona->getEdad();
    }
    
    return round($totalEdad / count($personas), 1);
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Personas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* (Mantener todo el CSS anterior) */
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
        }

        .card-title {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #e01e6d;
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #3ab8d8;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background: #e6891e;
            transform: translateY(-2px);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .action-result {
            background: #e7f3ff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-male {
            background: #4cc9f0;
            color: white;
        }

        .badge-female {
            background: #f72585;
            color: white;
        }

        .badge-other {
            background: var(--warning);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .card {
                padding: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ===== Alertas en ROJO ===== */
        .error-message {
            color: #dc2626 !important;
            display: none;
            font-size: 0.8rem !important;
            margin-top: 5px;
            padding: 5px 8px;
            background: #fef2f2;
            border-radius: 4px;
            border-left: 3px solid #dc2626;
            font-weight: 500;
        }

        .input-error {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
            background-color: #fef2f2;
        }

        .input-success {
            border-color: #16a34a !important;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1) !important;
        }

        .btn-danger {
            background: #dc2626 !important;
            border: 1px solid #dc2626 !important;
        }

        .btn-danger:hover {
            background: #b91c1c !important;
            border-color: #b91c1c !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-warning {
            background: #ea580c !important;
            border: 1px solid #ea580c !important;
        }

        .btn-warning:hover {
            background: #c2410c !important;
            border-color: #c2410c !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-users"></i>Sistema de Gestión de Personas</h1>
            <p>Gestiona y organiza la información de personas de manera eficiente</p>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($personas); ?></div>
                <div class="stat-label">Total Personas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo contarHombres($personas); ?></div>
                <div class="stat-label">Hombres</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo contarMujeres($personas); ?></div>
                <div class="stat-label">Mujeres</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo calcularEdadPromedio($personas); ?></div>
                <div class="stat-label">Edad Promedio</div>
            </div>
        </div>

        <?php if ($mensajeExito): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $mensajeExito; ?>
            </div>
        <?php endif; ?>

        <!-- Lista de Personas -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-list"></i> Lista de Personas
                <span style="margin-left: auto; font-size: 1rem; color: var(--gray);">
                    <?php echo count($personas); ?> registros
                </span>
            </h2>

            <?php if (count($personas) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Contacto</th>
                                <th>Fecha Nac.</th>
                                <th>Edad</th>
                                <th>Género</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personas as $index => $persona): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($persona->getNombreCompleto()); ?></strong>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($persona->getEmail()); ?></div>
                                    <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($persona->getTelefono()); ?></div>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($persona->getFechaNacimiento())); ?></td>
                                <td>
                                    <span class="badge"><?php echo $persona->getEdad(); ?> años</span>
                                </td>
                                <td>
                                    <?php 
                                    $badgeClass = [
                                        'M' => 'badge-male',
                                        'F' => 'badge-female',
                                        'O' => 'badge-other'
                                    ][$persona->getGenero()] ?? 'badge-other';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $persona->getGeneroTexto(); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-danger" 
                                        onclick="confirmarEliminacion(<?php echo $index; ?>, '<?php echo $persona->getNombreCompleto(); ?>')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Botón para eliminar todos -->
                <div style="text-align: center; margin-top: 20px;">
                <button class="btn btn-warning" onclick="confirmarEliminacionTotal()">
                    <i class="fas fa-trash-alt"></i> Restablecer a Datos por Defecto
                </button>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h3>No hay personas registradas</h3>
                    <p>Agrega la primera persona usando el formulario below.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Acciones -->
        <div class="card">
            <h2 class="card-title"><i class="fas fa-play-circle"></i> Acciones de Personas</h2>
            <div class="actions-grid">
                <button class="btn btn-primary" onclick="realizarAccion('comer')">
                    <i class="fas fa-utensils"></i> Comer
                </button>
                <button class="btn btn-primary" onclick="realizarAccion('caminar')">
                    <i class="fas fa-walking"></i> Caminar
                </button>
                <button class="btn btn-primary" onclick="realizarAccion('hablar')">
                    <i class="fas fa-comments"></i> Hablar
                </button>
                <button class="btn btn-primary" onclick="realizarAccion('dormir')">
                    <i class="fas fa-bed"></i> Dormir
                </button>
                <button class="btn btn-primary" onclick="realizarAccion('estudiar')">
                    <i class="fas fa-graduation-cap"></i> Estudiar
                </button>
            </div>
            <div id="resultados"></div>
        </div>

        <!-- Formulario para Nueva Persona -->
        <div class="card">
            <h2 class="card-title"><i class="fas fa-user-plus"></i> Agregar Nueva Persona</h2>
            <form method="POST" action="" id="formPersona" onsubmit="return validarFormulario(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ingrese el nombre" 
                            value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                            oninput="validarCampo(this, 'nombre')">
                        <small class="error-message" id="errorNombre" style="color: #f72585; display: none; font-size: 0.8rem;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Apellido *</label>
                        <input type="text" name="apellido" class="form-control" placeholder="Ingrese el apellido"
                            value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>"
                            oninput="validarCampo(this, 'apellido')">
                        <small class="error-message" id="errorApellido" style="color: #f72585; display: none; font-size: 0.8rem;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha de Nacimiento *</label>
                        <input type="date" name="fechaNacimiento" class="form-control"
                            value="<?php echo htmlspecialchars($_POST['fechaNacimiento'] ?? ''); ?>"
                            onchange="validarCampo(this, 'fechaNacimiento')">
                        <small class="error-message" id="errorFecha" style="color: #f72585; display: none; font-size: 0.8rem;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="ejemplo@email.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            oninput="validarCampo(this, 'email')">
                        <small class="error-message" id="errorEmail" style="color: #f72585; display: none; font-size: 0.8rem;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono *</label>
                        <input type="tel" name="telefono" class="form-control" placeholder="Número de teléfono"
                            value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>"
                            oninput="validarCampo(this, 'telefono')">
                        <small class="error-message" id="errorTelefono" style="color: #f72585; display: none; font-size: 0.8rem;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Género *</label>
                        <select name="genero" class="form-control" onchange="validarCampo(this, 'genero')">
                            <option value="">Seleccionar género</option>
                            <option value="M" <?php echo ($_POST['genero'] ?? '') == 'M' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="F" <?php echo ($_POST['genero'] ?? '') == 'F' ? 'selected' : ''; ?>>Femenino</option>
                            <option value="O" <?php echo ($_POST['genero'] ?? '') == 'O' ? 'selected' : ''; ?>>Otro</option>
                        </select>
                        <small class="error-message" id="errorGenero" style="color: #f72585; display: none; font-size: 0.8rem;"></small>
                    </div>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                    <i class="fas fa-plus-circle"></i> Crear Nueva Persona
                </button>
            </form>
        </div>

    <!-- Footer -->
<footer style="
    background: linear-gradient(180deg, #f9f9f9, #ececec);
    color: #222;
    text-align: center;
    padding: 15px 10px;
    margin-top: 40px;
    font-family: 'Poppins', sans-serif;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    font-size: 0.9rem;
    letter-spacing: 0.5px;
">
    <p style="
        margin: 0;
        font-weight: 600;
        color: #1a1a1a;
        text-shadow: 0 0 2px rgba(255,255,255,0.8);
    ">
        &copy; 2025 <span style="color: #0078ff;">Jair Alfonso Arias Cueca</span>. Todos los derechos reservados.
    </p>
</footer>

 <!-- Incluir archivo de validaciones -->
    <script src="validaciones.js"></script>

    <script>
    function realizarAccion(accion) {
        const resultadosDiv = document.getElementById('resultados');
        resultadosDiv.innerHTML = '<h4 style="margin-bottom: 15px; color: var(--primary);">📝 Resultados de la acción: ' + accion + '</h4>';
        
        <?php 
        $personasArray = [];
        foreach ($personas as $persona) {
            $personasArray[] = $persona->toArray();
        }
        echo 'const personasData = ' . json_encode($personasArray) . ';';
        ?>
        
        // Diccionario para gerundios correctos
        const gerundios = {
            'comer': 'comiendo',
            'caminar': 'caminando',
            'hablar': 'hablando',
            'dormir': 'durmiendo',
            'estudiar': 'estudiando'
        };
        
        const gerundio = gerundios[accion] || accion + 'ando';
        
        personasData.forEach((persona, index) => {
            const hora = new Date().toLocaleTimeString();
            const mensaje = persona.nombreCompleto + ' está ' + gerundio;
            
            const div = document.createElement('div');
            div.className = 'action-result';
            div.innerHTML = `
                <strong>${persona.nombreCompleto}</strong> 
                <span style="color: var(--gray);">(${persona.edad} años)</span><br>
                <em>${mensaje}</em><br>
                <small style="color: var(--gray);"><i class="fas fa-clock"></i> Hora: ${hora}</small>
            `;
            resultadosDiv.appendChild(div);
        });
    }

    // Scroll suave para mensajes de éxito
    <?php if ($mensajeExito): ?>
        setTimeout(() => {
            document.querySelector('.alert-success')?.scrollIntoView({ 
                behavior: 'smooth' 
            });
        }, 100);
    <?php endif; ?>
    </script>

</body>
</html>
