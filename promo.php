<?php
session_start();

require_once 'db_connection.php';

// Redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: index.php");
  exit;
}

// Fetch promotions with pilot names from the database
$sql_promotions = "SELECT promo.id, promo.name, promo.start_date, promo.end_date, user.first_name, user.last_name, promo.pilote_user_id
                   FROM promo
                   INNER JOIN user ON promo.pilote_user_id = user.id";
$result_promotions = mysqli_query($conn, $sql_promotions);
$promotions = mysqli_fetch_all($result_promotions, MYSQLI_ASSOC);
mysqli_free_result($result_promotions);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<title>Chat DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="promo.css" />

<body>
  <!-- Bloc Navigation -->
  <div class="container" id="bloc-nav">
    <nav class="barre-nav">
      <div class="navbar-header">
        <a class="renvoi-accueil" href="accueil.php">Di-Lab</a>
      </div>

      <div class="collapse navbar-collapse navbar-1">
        <ul class="site-navigation nav">
          <li><a href="#">Promos</a></li>
          <li><a href="group.php">Groupe</a></li>
          <li><a href="#">Projects</a></li>
          <li><a href="chat.php?conversation_id=0">Chat</a></li>
          <li><a href="userlist.php">Utilisateur</a></li>
          <li>
            <a href="#">
              <form method="post" enctype="multipart/form-data" action="logout.php">
                <button type="submit" class="bouton-logout" name="logout">Se déconnecter</button>
              </form>
            </a>
          </li>
        </ul>
      </div>
    </nav>
  </div>

  <div class="container">
    <div class="table-plus-recherche">
      <h1>PROMOS</h1>
      <div class="recherche-table">
        <h2>Filtres</h2>
        <div class="recherche-inputs">
          <input type="text" id="filter-name" placeholder="Filter By Name" class="form-control" style="max-width:120px;">
          <input type="date" id="filter-start-date" placeholder="Filter By Start Date" class="form-control" style="max-width:120px;">
          <input type="date" id="filter-end-date" placeholder="Filter By End Date" class="form-control" style="max-width:120px;">
          <select id="filter-pilot" class="form-control" style="max-width:120px;">
            <option value="">All Pilots</option>
            <?php foreach ($pilots as $pilot) : ?>
              <option value="<?php echo $pilot['id']; ?>"><?php echo $pilot['first_name'] . ' ' . $pilot['last_name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="cadre-table-scroll">
          <table id="filter" class="table-promos">
            <thead>
              <tr>
                <th>Nom promotion</th>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Pilote</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($promotions as $promotion) : ?>
                <tr>
                  <td><?php echo $promotion['name']; ?></td>
                  <td><?php echo $promotion['start_date']; ?></td>
                  <td><?php echo $promotion['end_date']; ?></td>
                  <td data-pilot-id="<?php echo $promotion['pilote_user_id']; ?>"><?php echo $promotion['first_name'] . ' ' . $promotion['last_name']; ?></td>
                  <td><a href="edit_promo.php?id=<?php echo $promotion['id']; ?>">Edit</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="create-promo-button">
      <form method="post" action="create_promo.php">
        <button type="submit" class="create-promo-btn">Create Promo</button>
      </form>
    </div>
  </div>

  <script src="TableFilter.min.js" defer></script>
  <script src="TableFilter.js" defer></script>
  <script>
    // Add event listeners to filter inputs
    document.getElementById("filter-name").addEventListener("input", function() {
      var filterText = this.value.toLowerCase();
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoName = row.querySelector("td:nth-child(1)").innerText.toLowerCase();
        var showRow = promoName.includes(filterText);
        row.style.display = showRow ? "table-row" : "none";
      });
    });

    document.getElementById("filter-start-date").addEventListener("input", function() {
      var filterStartDate = this.value;
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoStartDate = row.querySelector("td:nth-child(2)").innerText;
        var showRow = promoStartDate >= filterStartDate || filterStartDate === "";
        row.style.display = showRow ? "table-row" : "none";
      });
    });

    document.getElementById("filter-end-date").addEventListener("input", function() {
      var filterEndDate = this.value;
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoEndDate = row.querySelector("td:nth-child(3)").innerText;
        var showRow = promoEndDate <= filterEndDate || filterEndDate === "";
        row.style.display = showRow ? "table-row" : "none";
      });
    });

    document.getElementById("filter-pilot").addEventListener("change", function() {
      var filterPilotId = this.value;
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoPilotId = row.querySelector("td:nth-child(4)").getAttribute("data-pilot-id");
        var showRow = filterPilotId === "" || promoPilotId === filterPilotId;
        row.style.display = showRow ? "table-row" : "none";
      });
    });
  </script>
</body>

</html>
