<?php

// Validate the API key
require_once '../validate_api_key.php';

// Enforce a POST request
require_once '../require_post_method.php';

// Get the domain name from the request
$domain_name = mysqli_real_escape_string($mysqli, $_POST['domain_name']);

// Default
$update_count = false;

// Ensure the domain name is provided
if (!empty($domain_name)) {
    // Fetch the domain based on the name and client_id
    $domain_row = mysqli_fetch_assoc(
        mysqli_query(
            $mysqli,
            "SELECT * FROM domains WHERE domain_name = '$domain_name' AND domain_client_id = $client_id LIMIT 1"
        )
    );

    if ($domain_row) {
        // Variable assignment from POST - assigning the current database value if a value is not provided
        $name = isset($_POST['name']) ? mysqli_real_escape_string($mysqli, $_POST['name']) : $domain_row['domain_name'];
        $expiration_date = isset($_POST['expiration_date']) ? mysqli_real_escape_string($mysqli, $_POST['expiration_date']) : $domain_row['domain_expiration_date'];
        $status = isset($_POST['status']) ? mysqli_real_escape_string($mysqli, $_POST['status']) : $domain_row['domain_status'];
        $notes = isset($_POST['notes']) ? mysqli_real_escape_string($mysqli, $_POST['notes']) : $domain_row['domain_notes'];

        // Update query
        $update_sql = mysqli_query(
            $mysqli,
            "
            UPDATE domains 
            SET 
                domain_name = '$name',
                domain_expiration_date = '$expiration_date',
                domain_status = '$status',
                domain_notes = '$notes'
            WHERE 
                domain_name = '$domain_name' 
                AND domain_client_id = $client_id
            LIMIT 1
        "
        );

        // Check if the update was successful
        if ($update_sql) {
            $update_count = mysqli_affected_rows($mysqli);

            // Logging the action
            logAction("Domain", "Edit", "$name updated via API ($api_key_name)", $client_id);
            logAction("API", "Success", "Edited domain $name via API ($api_key_name)", $client_id);
        }
    } else {
        // Log a failed attempt to find the domain
        logAction("API", "Failure", "Attempted to edit domain $domain_name via API ($api_key_name), but it was not found", $client_id);
    }
}

// Output the result
require_once '../update_output.php';
