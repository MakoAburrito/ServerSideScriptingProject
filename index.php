<?php
$title = 'Excursion Planning Project';
?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="css/style.css">
        <title><?php echo $title; ?></title>
    </head>
    <body>
        <header>
            <h1><?php echo $title; ?></h1>
        </header>
        <nav>
            <ul>
                <!--
                References to parsing query urls:
                    https://www.php.net/manual/en/function.parse-url.php
                    https://www.quora.com/ExpressJS-What-is-the-difference-between-route-parameter-and-query-string-And-when-to-use-which
                    https://www.youtube.com/watch?v=sLnwdjQQlIw
                Explaining the code:
                    Creating a single page to handle all 5 tables to manage the CRUD operations, with sql queries we can use the GET[] to grab the related table and fields.
                -->
                <li><a href="crud.php?table=excursions&fields=id,name,timeframe,start_datetime">Manage Excursions</a></li>
                <li><a href="crud.php?table=events&fields=id,excursion_id,name,start_offset,duration">Manage Events</a></li>
                <li><a href="crud.php?table=holidays&fields=holiday_id,name,holiday_date">Manage Holidays</a></li>
                <li><a href="crud.php?table=scheduled_excursions&fields=id,excursion_id,start_datetime,end_datetime">Manage Scheduled Excursions</a></li>
                <li><a href="crud.php?table=scheduled_events&fields=id,scheduled_excursion_id,event_id,start_datetime,end_datetime,holiday_warning">Manage Scheduled Events</a></li>
                <li><a href="excursiondates.php">Generate Excursion Dates</a></li>
                <li><a href="demodata.php">Insert Demo Data</a></li>
                <li><a href="dropdatabase.php">Drop the Database</a></li>
            </ul>
            <hr>
        </nav>
        <main>
            <p>Welcome to the Excursion Planning Project Home Page</p>
            <hr>
            <p><a href="images/Iteration_FinalERD.png" target="_blank">View ERD Diagram</a></p>
        </main>
        <footer>
            &copy; 2024 Broward College - Melissa De Jesus, Drew Ali, Matthew Balzora
        </footer>
    </body>
</html>


