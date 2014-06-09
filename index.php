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
$manager = new Fuel\Auth\Manager(
	new Fuel\Auth\Storage\File('/tmp'),
	new Fuel\Auth\Persistence\File('/tmp')
);

// assign our Auth drivers
$manager->addDriver(new Fuel\Auth\User\File('/tmp'), 'user');
#$manager->addDriver(new Fuel\Auth\Group\Null, 'group');
#$manager->addDriver(new Fuel\Auth\Role\Null, 'role');
#$manager->addDriver(new Fuel\Auth\Acl\Null, 'acl');

// TEST: create a user to test with
echo "TEST: USER CREATE",PHP_EOL;
$result = $manager->create('admin', 'password', array('salt' => 'ThIsIsAsALt', 'group' => 10, 'fullname' => 'Administrator'));
checkResult($result, "Created new user with id: %d", "Failed creating a new user: ");

// TEST: login with this user
echo "TEST: USER LOGIN",PHP_EOL;
$result = $manager->login('admin', 'password');
checkResult($result, "User with id: %d logged in", "Failed to login user: ");

// TEST: verify the user id
echo "TEST: USER GETUSERID",PHP_EOL;
checkResult($manager->getUserId(), "New user has unified id: %d", "Mismatch detected when fetching the unified user id:");

// TEST: logout
echo "TEST: USER LOGOUT",PHP_EOL;
$result = $manager->logout();

// TEST: delete the test user
echo "TEST: USER DELETE",PHP_EOL;
$result = $manager->delete('admin');
checkResult($result, "Deleted user with id: %d", "Failed deleting the user: ");
