<?php
/* 	
 * 	Acest fisier implementeaza REST API Endpoint-ul
 * 		POST /permissions/check
 *
 *  Request Body:
 *   	{ "user_id": "string", "path": "string", "operation": "string" }
 *  Response Body:
 * 		{ "permitted": bool, "message": "string" }
 *
 *	Functionalitate:
 * 		Verifica daca un utilizator cu id egal cu `user_id` are voie sa execute
 * 		operatia `operation` pe fisier/folder-ul care are calea `path`.
 *
 * 
 *	Detalii de implementare:
 *		In loc sa folosim o solutie de stocare persistenta pentru permisiuni, 
 *		folosim array-ul in-memory $permissions.
 *
 * 	Path Pattern:
 * 		Simbolul * poate inlocui doar nume complete de foldere sau fisiere. 
 *  	Poate tine locul la mai multe foldere. 
 * */

$permissions = [
	array('id' => 1, 'granter_user_id' => 1, 'recipient_user_id' => 2, 'path_pattern' => '/1/justa.pdf', 'permission_type' => 'rw'),
	array('id' => 4, 'granter_user_id' => 2, 'recipient_user_id' => 1, 'path_pattern' => '/2/secrets.txt', 'permission_type' => 'r'),
	array('id' => 2, 'granter_user_id' => 1, 'recipient_user_id' => 2, 'path_pattern' => '/1/photos/*', 'permission_type' => 'r'),
	array('id' => 3, 'granter_user_id' => 1, 'recipient_user_id' => 2, 'path_pattern' => '/1/photos/*', 'permission_type' => 'rw'),
    array('id' => 5, 'granter_user_id' => 2, 'recipient_user_id' => 1, 'path_pattern' => '/1/some_folder/*/README.md', 'permission_type' => 'r')
];

$types_of_permissions = ['r', 'rw'];

$method = strtolower($_SERVER['REQUEST_METHOD']);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

//Route The Request
if ($method == 'post' && preg_match('#^/permissions/check$#', $uri)) {
	$body = file_get_contents('php://input');
	$data = json_decode($body, true);
	permissions_check($data);
} else if ($method == 'get'  && preg_match('#^/permissions$#', $uri)) {
	header('Content-Type: application/json');
	header("HTTP/1.1 200 OK");
	echo json_encode($permissions);
	exit();	
} else {
	header('Content-Type: application/json');
	header("HTTP/1.1 404 Not Found");
	echo json_encode(['error' => 'Route not defined']);
	exit();
}

function select_user_permissions($user_id) {
	global $permissions;

	$user_permissions = array_filter($permissions, function($perm) use ($user_id) {
		return $perm['recipient_user_id'] === $user_id;
	});

	$filtered = array_map(function($perm) {
		return [
			'path_pattern' => $perm['path_pattern'],
			'permission_type' => $perm['permission_type']
		];
	}, $user_permissions);

	return $filtered;
}

function permissions_check($data) {
	global $types_of_permissions;

    // TODO: implementati aici 
    header('Content-Type: application/json');
    header("HTTP/1.1 501 Not Implemented");
    echo json_encode(['error' => 'Not implemented']);
    exit();
};
