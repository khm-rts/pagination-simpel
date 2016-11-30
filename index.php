<?php
// Inkludér fil der etablerer forbindelse til databasen (i variablen $link)
require 'db_config.php';
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Pagination</title>
	<style>
		/* Styling til links i pagination */
		.pagination {
			margin: 10px 0;
		}
		.pagination > a {
			border: 1px solid #000;
			color: #000;
			display: inline-block;
			margin-right: 5px;
			padding: 2px 8px;
			text-decoration: none;
		}

		.pagination > a.active {
			background-color: #CCC;
			padding: 4px 10px;
		}
	</style>
</head>
<body>
	<h1>Pagination</h1>

	<?php
	// Forespørgsel til at hente alle aktive produkter fra databasen
	$query =
		"SELECT
			produkt_id, produkt_varenr, produkt_navn, produkt_beskrivelse, produkt_pris, kategori_navn, producent_navn
		FROM
			produkter
		LEFT JOIN
			kategorier ON produkter.fk_kategori_id = kategorier.kategori_id
		LEFT JOIN
			producenter ON produkter.fk_producent_id = producenter.producent_id
		WHERE
			produkt_status = 1";

	// Send forespørgsel af produkter til databasen med mysqli_query(). Hvis der er fejl heri, stoppes videre indlæsning og fejlbesked vises
	$result = mysqli_query($link, $query) or die( mysqli_error($link) . '<pre>' . $query . '</pre>' . 'Fejl i forespørgsel på linje: ' . __LINE__ . ' i fil: ' . __FILE__);

	// Brug mysqli_num_rows() til at se hvor mange rækker der er i vores resultat
	$produkter_i_alt		= mysqli_num_rows($result);

	// Standardside der startes på, når der ikke er angivet side i URL parametre
	$aktuel_side 			= 1;

	// Hvis side er defineret i URL parametre henter vi værdi heraf og overskriver variablen $aktuel_side
	if ( isset($_GET['side']) )
	{
		$aktuel_side = $_GET['side'];
	}

	$produkter_per_side		= 3;

	// Beregn hvor mange sider der skal springes over, ved at tage den aktuelle side minus 1 og gange med produkter per sider. Hvis vi f.eks. er på side 3: (3-1) * 2 = 4. Så der skal springes 4 produkter over på side 3, hvilket passer, da vi har set de første 2 produkter på side 1, og de næste 2 på side 2
	$produkter_spring_over	= ($aktuel_side - 1) * $produkter_per_side;

	// Sorter produkter efter kategori, dernæst pris og begræns udtræk (LIMIT) til 2 produkter per side samt spring over (OFFSET) det beregnede antal produkter
	$query .=
		"
		ORDER BY
			kategori_navn, produkt_pris
		LIMIT
			$produkter_per_side
		OFFSET
			$produkter_spring_over";

	// Send forespørgsel til databasen med mysqli_query(). Hvis der er fejl heri, stoppes videre indlæsning og fejlbesked vises
	$result = mysqli_query($link, $query) or die( mysqli_error($link) . '<pre>' . $query . '</pre>' . 'Fejl i forespørgsel på linje: ' . __LINE__ . ' i fil: ' . __FILE__);

	// Vis antallet af produkter med funktionen mysqli_num_rows() der returnerer antaller af rækker fra resultat ($result)
	echo '<h2>Viser ' . mysqli_num_rows($result) . ' produkter af ' . $produkter_i_alt . '</h2>';

	// mysqli_fetch_assoc() returner data fra forespørgslen som et assoc array og vi gemmer data i variablen $row. Brug while til at løbe igennem alle rækker med produkter fra databasen
	while( $row = mysqli_fetch_assoc($result) )
	{
		?>
		<hr>
		<h3><?php echo $row['produkt_navn'] ?></h3>
		Varenr. <?php echo $row['produkt_varenr'] ?>
		<br><?php echo substr($row['produkt_beskrivelse'], 0, 100) . '...' // Brug substr() til kun at vise de første 100 karakterer af produktets beskrivelse ?>
		<br><strong><?php echo number_format($row['produkt_pris'], 2, ',', '.') // Brug number_format() til at formatere prisen med 2 decimaler, komma til adskillelse af decimaler og punktum for hvert tusinde i beløb. F.eks. 123.456,78 ?> kr.</strong>
		<?php
	}

	// Hvis der er flere produkter i alt, end der skal vises per side, skal vi vise links til sidenavigation
	if ($produkter_i_alt > $produkter_per_side)
	{
		?>
		<div class="pagination">
		<?php
		// Beregn hvor mange sider der skal være i alt, ved at dividere antal produkter i alt med hvor mange der skal vises per side
		$sider_i_alt = $produkter_i_alt / $produkter_per_side;

		// Brug en for-løkke til at genere sidelinks med start på side 1, til det beregnede antal sider i alt og forøg med 1 side hver gang løkken kører
		for($side = 1; $side <= $sider_i_alt; $side++)
		{
			?>
			<a href="index.php?side=<?php echo $side ?>"<?php if ($side == $aktuel_side) echo ' class="active"'; // Hvis side er lig den aktuelle side, tilføjes klassen active ?>>
				<?php echo $side ?>
			</a>
			<?php
		}
		?>
		</div>
		<?php
	}
	?>
</body>
</html>
<?php
// Inkludér fil, der lukker forbindelsen til databasen og tømmer vores output buffer
require 'db_close.php';