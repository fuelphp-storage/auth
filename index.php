<?php
// make sure we've got autoloading
require __DIR__."/vendor/autoload.php";

// function to aid in debugging
function checkResult($result, $success, $failure)
{
	global $manager;

	if ($result)
	{
		echo "* ", sprintf($success, $result), PHP_EOL;
		if (is_array($result))
		{
			var_export($result); echo PHP_EOL;
		}
	}
	else
	{

		echo "* ", $failure, PHP_EOL;
		foreach ($manager->lastErrors() as $name => $error)
		{
			echo "* ", "- Driver: ",$name, ", Message: ",$error->getMessage(),PHP_EOL;
		}
	}
}

// create an Auth manager instance
$manager = new \Fuel\Auth\Manager(
	new \Fuel\Auth\Storage\File(['file' => '/tmp']),
	new \Fuel\Auth\Persistence\File(['file' => '/tmp'])
);

// assign our Auth drivers
$manager->addDriver(new \Fuel\Auth\User\File(['min_password_length' => 6, 'new_password_length' => 8, 'file' => '/tmp']), 'user');
$manager->addDriver(new \Fuel\Auth\Group\File(['file' => '/tmp']), 'group');
$manager->addDriver(new \Fuel\Auth\Role\File(['file' => '/tmp']), 'role');
$manager->addDriver(new \Fuel\Auth\Acl\File(['file' => '/tmp']), 'acl');

// TEST: create a user to test with
echo "TEST: USER CREATE",PHP_EOL;
$result = $manager->createUser('admin', 'password', array('salt' => 'ThIsIsAsALt', 'group' => 10, 'fullname' => 'Administrator', 'email' => 'admin@example.org'));
checkResult($result, "Created new user with id: %d", "Failed creating a new user: ");

// TEST: login with this user
echo "TEST: USER LOGIN",PHP_EOL;
$result = $manager->login('admin', 'password');
checkResult($result, "User with id: %d logged in", "Failed to login user: ");

// TEST: get the unified user id
echo "TEST: USER GETUSERID",PHP_EOL;
checkResult($userid = $manager->getUserId(), "New user has unified id: %d", "Mismatch detected when fetching the unified user id:");

// TEST: get the unified user id
echo "TEST: USER GETID",PHP_EOL;
checkResult($manager->getId(), "New user has internal id: %d", "Mismatch detected when fetching the internal user id:");

// TEST: get the user email
echo "TEST: USER GETEMAIL",PHP_EOL;
checkResult($email1 = $manager->getEmail(), "New user has email address: %s", "Unable to get the users email address");

// TEST: get atttributes
echo "TEST: USER GET FULLNAME",PHP_EOL;
checkResult($manager->get('fullname'), "User is registered as : %s", "Unable to get the users fullname");

// TEST: get atttributes
echo "TEST: USER GET USERNAME",PHP_EOL;
checkResult($manager->get('username'), "User is registered as : %s", "Unable to get the users name");
checkResult($manager->get('username') == $manager->getName(), "Result matches getName()", "Result doesn't match getName()");

// TEST: get atttributes
echo "TEST: USER GET UNKNOWN",PHP_EOL;
checkResult($manager->get('unknown'), "Unknown attribute check failed", "Unknown attribute check succeeded");

// TEST: check this user
echo "TEST: CHECK LOGIN 1",PHP_EOL;
checkResult($manager->check(), "Check succeeded", "Check failed");

// TEST: check this user logged-in state
echo "TEST: VERIFY LOGIN",PHP_EOL;
checkResult($manager->isLoggedIn(), "isLoggedIn Check succeeded", "isLoggedIn Check failed");

// TEST: logout
echo "TEST: USER LOGOUT",PHP_EOL;
$result = $manager->logout();

// TEST: check this user
echo "TEST: CHECK LOGIN 2",PHP_EOL;
checkResult( ! $manager->check(), "Check succeeded", "Check failed");

// TEST: password change without a user
echo "TEST: CHANGE PASSWORD 1",PHP_EOL;
checkResult($manager->password('a'), "Password changed without a user?", "Unable to change the password:");

// TEST: force login user 1
echo "TEST: FORCE LOGIN",PHP_EOL;
checkResult($manager->forceLogin($userid), "Force login succeeded", "Force login failed");

// TEST: password change with invalid password
echo "TEST: CHANGE PASSWORD 2",PHP_EOL;
checkResult($manager->password('a'), "Password changed that is to short?", "Unable to change the password:");

// TEST: password change with valid password
echo "TEST: CHANGE PASSWORD 3",PHP_EOL;
$p1 = $manager->get('password');
checkResult($manager->password('abcdef'), "Password changed", "Unable to change the password:");
$p2 = $manager->get('password');
checkResult($p1 !== $p2, "Password change validated", "Password wasn't changed");

// TEST: password reset
echo "TEST: RESET PASSWORD",PHP_EOL;
checkResult($newpass = $manager->reset(), "Password reset", "Unable to reset the password:");
$p3 = $manager->get('password');
checkResult($p2 !== $p3, "Password reset validated", "Password wasn't reset");

echo "TEST: SHADOW LOGIN EMULATION",PHP_EOL;
checkResult($manager->shadowLogin(), "Successful shadow login emulated for user: %d", "Shadow login failed:");

// TEST: change the users email
echo "TEST: UPDATEUSER",PHP_EOL;
$email2 = $manager->updateUser(null, array('email' => 'admin@example.com'));
checkResult($email1 !== $email2, "Email address succesfully changed", "Unable to change email address");

// TEST: validate user and password
echo "TEST: VALIDATE USER",PHP_EOL;
checkResult( ! $manager->validate('admin', 'rubbish'), "Invalid password handled correctly", "Unable to validate user and password");
checkResult($manager->validate('admin', $newpass), "User correctly validated", "Unable to validate user and password");

// TEST: empty group test
echo "TEST: NO GROUPS",PHP_EOL;
checkResult($manager->getAllGroups() == [], "Group driver responded correctly", "Incorrect result from initial getAllGroups call");

// TEST: create test group
echo "TEST: CREATE GROUP",PHP_EOL;
checkResult($manager->createGroup('test', []), "Successful created test group with id: %d", "Test group creation failed:");

// TEST: assign test group to user
echo "TEST: ASSIGN GROUP",PHP_EOL;
checkResult($manager->assignUserToGroup('test', $userid), "Successful assigned test group", "Test group assignment failed:");

// TEST: empty role test
echo "TEST: NO ROLES",PHP_EOL;
checkResult($manager->getAllRoles() == [], "Role driver responded correctly", "Incorrect result from initial getAllRoles call");

// TEST: create test role
echo "TEST: CREATE ROLE",PHP_EOL;
checkResult($manager->createRole('test', []), "Successful created test role with id: %d", "Test role creation failed:");

// TEST: assign test role to user
echo "TEST: ASSIGN ROLE",PHP_EOL;
checkResult($manager->assignUserToRole('test', $userid), "Successful assigned test role", "Test role assignment failed:");

// TEST: create a permission
echo "TEST: PERMISSION CREATION",PHP_EOL;
checkResult($manager->createPermission('module.function.admin', array('create', 'read', 'update', 'delete', 'view')), "Successful created permission", "Permission creation failed:");

// TEST: update a permission
echo "TEST: PERMISSION UPDATE",PHP_EOL;
checkResult($manager->updatePermission('module.function.admin', array('create', 'read', 'update', 'delete')), "Successful updated permission", "Permission update failed:");

// TEST: assign a permission to a role
echo "TEST: INCORRECT PERMISSION ASSIGNMENTS",PHP_EOL;
checkResult($manager->assignPermissionTo('role', 'test', 'unknown', array('read')), "Incorrect permission assignment failed", "Succesfully captured incorrect permission name:");
checkResult($manager->assignPermissionTo('role', 'unknown', 'module.function.admin', array('read')), "Incorrect permission assignment failed", "Succesfully captured incorrect role name:");
checkResult($manager->assignPermissionTo('bicyle', 'test', 'module.function.admin', array('read')), "Incorrect permission assignment failed", "Succesfully captured incorrect type name:");
checkResult($manager->assignPermissionTo('role', 'test', 'module.function.admin', array('unknown')), "Incorrect permission assignment failed", "Succesfully captured incorrect action list:");

echo "TEST: PERMISSION ASSIGNMENT TO A ROLE",PHP_EOL;
checkResult($manager->assignPermissionTo('role', 'test', 'module.function.admin', array('read')), "Successful assigned permission", "Permission assignment failed:");

// TEST: assign a permission to a group
echo "TEST: PERMISSION ASSIGNMENT TO A GROUP",PHP_EOL;
checkResult($manager->assignPermissionTo('group', 'test', 'module.function.admin', array('read')), "Successful assigned permission", "Permission assignment failed:");

// TEST: delete the test user
echo "TEST: USER DELETE",PHP_EOL;
$result = $manager->deleteUser($userid);
checkResult($result, "Deleted user with id: %d", "Failed deleting the user: ");

// TEST: delete test group
echo "TEST: DELETE GROUP",PHP_EOL;
checkResult($manager->deleteGroup('test'), "Successfully deleted the test group", "Test group deletion failed:");

// TEST: empty group test
echo "TEST: NO GROUPS 2",PHP_EOL;
checkResult($manager->getAllGroups() == [], "Group driver responded correctly", "Incorrect result from final getAllGroups call");

// TEST: delete test role
echo "TEST: DELETE ROLE",PHP_EOL;
checkResult($manager->deleteRole('test'), "Successfully deleted the test role", "Test role deletion failed:");

// TEST: empty role test
echo "TEST: NO ROLES 2",PHP_EOL;
checkResult($manager->getAllRoles() == [], "Role driver responded correctly", "Incorrect result from final getAllRoles call");

// TEST: delete a permission
echo "TEST: PERMISSION DELETE",PHP_EOL;
checkResult($manager->deletePermission('module.function.admin'), "Successful deleted permission", "Permission deletion failed:");

// TEST: check this user
echo "TEST: CHECK LOGIN 3",PHP_EOL;
checkResult($manager->check(), "Check failed", "Check succesfully completed");
