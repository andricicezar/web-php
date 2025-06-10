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

function helper_single_permission_check($path_pattern, $permission_type, $path, $operation) {
	// Base case: if both arrays are empty, match
	if (empty($path_pattern) && empty($path)) {
		// Permission type check
		if ($permission_type === $operation) {
			return true;
		}
		if ($permission_type === 'rw' && $operation === 'r') {
			return true;
		}
		return false;
	}

	// If pattern is empty but path is not, or vice versa, no match
	if (empty($path_pattern) || empty($path)) {
		return false;
	}

	// Wildcard: skip this segment in both pattern and path
	if ($path_pattern[0] === '*') {
		return helper_single_permission_check(array_slice($path_pattern, 1), $permission_type, array_slice($path, 1), $operation) ||
			   helper_single_permission_check($path_pattern, $permission_type, array_slice($path,1), $operation);
	}

	// If segments don't match, fail
	if ($path_pattern[0] !== $path[0]) {
		return false;
	}

	// Segments are equal, recurse for next segment
	return helper_single_permission_check(array_slice($path_pattern, 1), $permission_type, array_slice($path, 1), $operation);
}

function helper_permissions_check($user_permissions, $path, $operation) {
	$path = explode('/', $path);
	foreach ($user_permissions as $user_permission) {
		if (helper_single_permission_check(
				explode('/',$user_permission['path_pattern']),
				$user_permission['permission_type'],
				$path, 
				$operation)) {
			return true;
		}
	}
	return false;
}

function permissions_check($data) {
	global $types_of_permissions;

	if(!isset($data['user_id']) || !isset($data['path']) || !isset($data['operation'])) {
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad Request");
		echo json_encode(['error' => 'POST body is invalid']);
		exit();
	}

	$user_id = intval($data['user_id']);
	$path = $data['path'];
    $operation = $data['operation'];

	if (!in_array($operation, $types_of_permissions)) {
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad Request");
		echo json_encode(['error' => 'Operation is not in $types_of_permissions']);
		exit();	
	}

	$user_permissions = select_user_permissions($user_id);
	if (helper_permissions_check($user_permissions, $path, $operation)) {
		header('Content-Type: application/json');
		header("HTTP/1.1 200 OK");
		echo json_encode(['permitted' => true, 'message' => 'User is allowed to perform the operation.']);
		exit();	
	} else {
		header('Content-Type: application/json');
		header("HTTP/1.1 200 OK");
		echo json_encode(['permitted' => false, 'message' => 'User is not allowed to perform the operation.']);
		exit();			
	}
};