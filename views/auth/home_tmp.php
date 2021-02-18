

<?php 
include('server.php');
include('../../database.php');

if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: auth/login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<?php
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 1) {
        header('location: index.php');
    }
    
    $query = "SELECT id, name, description, prerequisite FROM topics";
    $results = mysqli_query($db, $query);
    $rows = mysqli_fetch_all($results, MYSQLI_ASSOC);
    
    $nodes = array();
    $nodeNum = array();
    $links = array();
    $i = 0;
    foreach ($rows as $row) {
        $nodes[] = array("name" => $row["name"], "symbol" => strval($row["id"]), "group" => $row["id"], "description" => "test");
        $nodeNum[$row["id"]] = $i;
        $i ++;
    }
    
    foreach ($rows as $row) {
        if ($row["prerequisite"]) {
            $links[] = array("source" => $nodeNum[$row["prerequisite"]], "target" => $nodeNum[$row["id"]], "value" => 1);
        }
    }
?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Course</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link href="home.css" rel="stylesheet">
    
    <!--for mind map-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://code.jquery.com/ui/jquery-ui-git.css" type="text/css" rel="stylesheet"/>
    <script src="https://code.jquery.com/ui/jquery-ui-git.js" type="text/javascript"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"> </script>
    
    <script type="text/javascript" src="http://mbostock.github.com/d3/d3.js?1.29.1"></script>
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-light bg-white">
	<div id="logo-img">
		<a href="home.php">
			<img src="img/unsw_0.png" href="home.php">
		</a>
	</div>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="course.php">Course</a>
			</li>
			<li class="nav-item">
				<a class="nav-link disabled" href="#"></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="login.php?logout='1'">Logout</a>	
			</li>
		</ul>
	</div>
	</nav>
    
	<script src="https://dagrejs.github.io/project/dagre-d3/latest/dagre-d3.js"></script>

    <div id="learning_map"></div>
</body>

<script>
    $(document).ready(function () {
        /* (c) Michiel van der Blonk
         * a model of basic phycics quantities
         * July 2012
         * some code adapted from http://bl.ocks.org/1153292
         */
        var width = 960,
            height = 700,
            r = 12,
            gravity = 0.1,
            distance = 100,
            charge = -800,
            fill = d3.scale.category10(),

            nodes=<?php echo json_encode($nodes); ?>,

            // the relations shown can be calculated using
            // formulas from either 1 or 2 other quantities
            links=<?php echo json_encode($links); ?>;

        // create the canvas for the model
        var svg = d3.select("body").append("svg")
            .attr("width", width)
            .attr("height", height);

        // d3 provides the calculations to animate the model
        var force = d3.layout.force()
            .gravity(gravity)
            .distance(distance)
            .charge(charge)
            .size([width, height]);

        // add data, and start the animation
        force.nodes(nodes)
            .links(links)
            .start();

        // add classnames to links for styling
        var link = svg.selectAll(".link")
            .data(links)
            .enter().append("line")
            .attr("class", "link");

        // enable drag of nodes
        var node = svg.selectAll(".node")
            .data(nodes)
            .enter().append("g")
            .attr("class", "node")
            .call(force.drag);

        // draw circles
        var circle=node.append("svg:circle").attr("r", r - .75).style("fill", function(d) {
                return fill(d.group);
            }).style("stroke", function(d) {
                return d3.rgb(fill(d.group)).darker();
            }).call(force.drag)
            .on("click", function(d) {
                window.location.href = "../topic/topic.php?id=" + d.group.toString();
            });

        // add tooltip so it shows the unit and formula
        circle.append("svg:title").text(function(d, i) {
            if (typeof d.description !== 'undefined') {
            return d.description;
            }
        });


        // create arrowheads (end markers)
        // three type of styles can be made for each group
        // this feature is not used
        svg.append("svg:defs").selectAll("marker")
            .data([1,2,3])
          .enter().append("svg:marker")
            .attr("id", String)
            .attr("viewBox", "0 -5 10 10")
            .attr("refX", 22)
            .attr("refY", -1.5)
            .attr("markerWidth", 6)
            .attr("markerHeight", 6)
            .attr("fill-color","#cccccc")
            .attr("orient", "auto")
          .append("svg:path")
            .attr("d", "M0,-5L10,0L0,5");

        // attach markers
        var path = svg.append("svg:g").selectAll("path")
            .data(force.links())
            .enter().append("svg:path")
            .attr("class", function(d) { return "link " + d.value; })
            .attr("marker-end", function(d) { return "url(#" + d.value + ")"; });

        // create a group for text elements
        var text = svg.append("svg:g").selectAll("g")
            .data(force.nodes())
            .enter().append("svg:g");

        // create shadow in white
        text.append("svg:text")
              .attr("dx", 12)
              .attr("dy", ".35em")
              .attr("class", "shadow")
              .text(function(d) { return d.name;}
          );

        // create name on top of shadow
        text.append("svg:text")
              .attr("dx", 12)
              .attr("dy", ".35em")
              .text(function(d) { return d.name;}
          );

        // put the symbol, e.g. kg inside the circle
        text.append("svg:text")
              .attr("dx", -4)
              .attr("dy", 2)
              .attr("fill", "#ffffff")
              .text(function(d) { return d["symbol"]?d.symbol:"";}
          );

        force.on("tick", tick);

        // move circles using force
        function tick() {
            path.attr("d", function(d) {
                var dx = d.target.x - d.source.x,
                    dy = d.target.y - d.source.y,
                    dr = 0; // straight lines (0=straight, 1=round)
                    // alternatively use dr = Math.sqrt(dx * dx + dy * dy); for similar arcs
                return "M" + d.source.x + "," + d.source.y + "A" + dr + "," + dr + " 0 0,1 " + d.target.x + "," + d.target.y;
            });

            circle.attr("transform", function(d) {
                return "translate(" + d.x + "," + d.y + ")";
            });

            text.attr("transform", function(d) {
                return "translate(" + d.x + "," + d.y + ")";
            });
        };
    });

	$('#exampleModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget) // Button that triggered the modal
		var recipient = button.data('whatever') // Extract info from data-* attributes
		// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		var modal = $(this)
		modal.find('.modal-title').text('New message to ' + recipient)
		modal.find('.modal-body input').val(recipient)
	});
</script>

</html>

