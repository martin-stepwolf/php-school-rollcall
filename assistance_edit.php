<?php
session_start();
require 'session_cache_expire().php';
// Comprobamos tenga sesion, si no entonces redirigimos y MATAMOS LA EJECUCION DE LA PAGINA.
if (isset($_SESSION['usuario'])) {
	try {
		$connection = new PDO('mysql:host=localhost;dbname=escuela_bd', 'root', '');
	} catch (PDOException $e) {
		echo "Error:" . $e->getMessage();
	}
	$statement = $connection->prepare('SELECT clases.id, materias.nombre, materias.descripcion, materias.grado, clases.hora FROM clases INNER JOIN materias ON clases.Id_materia=materias.Id WHERE clases.Id_maestro = :Id_maestro and materias.Id = :Id_clase');
	$statement->execute(array(
		':Id_maestro' => $_SESSION['usuario']['0'],
		':Id_clase' => filter_var(strtolower($_GET['id_clase']), FILTER_SANITIZE_STRING)
	));
	$user = $statement->fetch();
	if (!$user) {
		// verifica que la clase a la que quiere entrar el maestro le pertenezca
		// de lo contrario lo regresa al menu principal
		header('Location: login.php');
	} else {
		$statement2 = $connection->prepare('SELECT * FROM alumnos_clases INNER JOIN usuarios ON alumnos_clases.Id_alumno=usuarios.Id WHERE alumnos_clases.Id_clase = :Id_clase and alumnos_clases.Id_alumno = :Id_alumno');
		$statement2->execute(array(
			':Id_alumno' => filter_var(strtolower($_GET['id_alumno']), FILTER_SANITIZE_STRING),
			':Id_clase' => filter_var(strtolower($_GET['id_clase']), FILTER_SANITIZE_STRING)
		));
		$students = $statement2->fetch();
		if (!$students) {
			header('Location: login.php');
		} else
			if ($_POST != null) {
			foreach ($_POST as $date => $assistance) {
				// Recorre todos los registros para modificar las asistencias
				$connection = new PDO('mysql:host=localhost;dbname=escuela_bd', 'root', '');
				$statement = $connection->prepare('UPDATE asistencias SET Id_tipo_asistencia=:Asistencia WHERE Fecha=:Fecha and Id_alumno=:Id_alumno');
				$statement->execute(array(
					':Fecha' => $date,
					':Id_alumno' => filter_var(strtolower($_GET['id_alumno']), FILTER_SANITIZE_STRING),
					':Asistencia' => $assistance
				));
			}
			header('Location: assistance.php?id_clase=' . $_GET['id_clase']);
		}
		require 'views/asistencias_editar.view.php';
	}
} else {
	header('Location: ../login.php');
	die();
}
