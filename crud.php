<?php
include 'database.php';
$pdo->exec("USE excursions");

// Get the table name and fields from the URL parameters
// Example: crud.php?table=excursions&fields=id,name,timeframe,start_date which is located in the index.php file as a link
$table = $_GET['table'] ?? null; //Pulls from a list of tables in database
$fields = $_GET['fields'] ?? null; 

//Returns Error if tables or fields are missing.
if (!$table || !$fields) {
    die("<p>Error: Table or fields not specified.</p>");
} 

//splits fields into an array & takes ID as the primary key.
$fieldArray = explode(',', $fields);
$primaryKey = $fieldArray[0];

//reformats Datetime for user input.
function formatDatetime($value) {
    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    return $datetime ? $datetime->format('m/d/Y h:i A') : $value;
}


/* References on dynamic crud: 
    https://github.com/joshuahiwat/crud/blob/master/control/query_connector.class.php
    https://www.youtube.com/watch?v=zc-XwaUZkIg
    https://www.youtube.com/watch?v=syouNlHPPkE
    https://bootstrapfriendly.com/blog/php-pdo-prepared-statement-crud-crud-operations-in-php

*/

//  Function Record handliing (Adding, Editing, Deleting)
function handleFormSubmission($pdo, $table, $fields, $primaryKey) {
    $action = $_POST['action'] ?? null;
    if (!$action) return;

    //  Create a second array, without the action variable.
    $data = array_filter($_POST, fn($key) => $key !== 'action', ARRAY_FILTER_USE_KEY);

    //  Dynamically generate and executes SQL Queries
    if ($action === 'add') {
        //Changes Array to sring
        $columns = implode(',', array_keys($data));
        //Creates a second string for values.
        $placeholders = implode(',', array_map(fn($key) => ":$key", array_keys($data)));
        //Builds Dynamic INSERT query.
        $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->execute($data);
    } elseif ($action === 'update') {
        $setClause = implode(',', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $stmt = $pdo->prepare("UPDATE $table SET $setClause WHERE $primaryKey = :$primaryKey");
        $data[$primaryKey] = $_POST[$primaryKey];
        $stmt->execute($data);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE $primaryKey = :$primaryKey");
        $stmt->execute([$primaryKey => $_POST[$primaryKey]]);
    }
    header("Location: crud.php?table=$table&fields=$fields");
    exit;
}

//Runs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleFormSubmission($pdo, $table, $fields, $primaryKey);
}

// Fetch records and optionally a specific record for editing
$records = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
$editRecord = null;
if (isset($_GET['action'], $_GET[$primaryKey]) && $_GET['action'] === 'edit') {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $primaryKey = :$primaryKey");
    $stmt->execute([$primaryKey => $_GET[$primaryKey]]);
    $editRecord = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage <?= ucfirst($table) ?></title>
</head>
<body>
    <h1>Manage <?= ucfirst($table) ?></h1>
    <a href="index.php">Back to Home</a>

    <!-- Add/Edit Record Form -->
    <h2><?= $editRecord ? 'Update Record' : 'Add New Record' ?></h2>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $editRecord ? 'update' : 'add' ?>">
        <?php foreach ($fieldArray as $field): ?>
            <label><?= ucfirst($field) ?>:</label>
            <input 
                type="<?= in_array($field, ['start_datetime', 'end_datetime']) ? 'datetime-local' : 'text' ?>" 
                name="<?= $field ?>" 
                value="<?= htmlspecialchars($editRecord[$field] ?? '') ?>" 
                <?= $field === $primaryKey && $editRecord ? 'readonly' : 'required' ?>
            ><br>
        <?php endforeach; ?>
        <input type="submit" value="<?= $editRecord ? 'Update' : 'Add' ?>">
    </form>

    <!-- Existing Records Table -->
    <h2>Existing Records</h2>
    <?php if ($records): ?>
        <table border="1">
            <tr>
                <?php foreach ($fieldArray as $field): ?>
                    <th><?= ucfirst($field) ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
            <?php foreach ($records as $record): ?>
                <tr>
                    <?php foreach ($fieldArray as $field): ?>
                        <td><?= htmlspecialchars(in_array($field, ['start_datetime', 'end_datetime']) ? formatDatetime($record[$field]) : $record[$field]) ?></td>
                    <?php endforeach; ?>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="<?= $primaryKey ?>" value="<?= $record[$primaryKey] ?>">
                            <input type="submit" value="Delete">
                        </form>
                        <a href="crud.php?table=<?= $table ?>&fields=<?= $fields ?>&action=edit&<?= $primaryKey ?>=<?= $record[$primaryKey] ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No records found.</p>
    <?php endif; ?>
</body>
</html>
