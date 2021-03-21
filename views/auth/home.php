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
    
    $nodes = array();
    $nodeNum = array();
    $links = array();
    $i = 0;
    
    $query = "SELECT id, name FROM topics";
    $results = mysqli_query($db, $query);
    $topics = mysqli_fetch_all($results, MYSQLI_ASSOC);
    foreach ($topics as $row) {
        $nodes[] = array("name" => $row["name"], "id" => $row["id"]);
        $nodeNum[$row["id"]] = $i;
        $i ++;
    }
    
    $query = "SELECT topic, prerequisite FROM prerequisites";
    $results = mysqli_query($db, $query);
    $rows = mysqli_fetch_all($results, MYSQLI_ASSOC);
    foreach ($rows as $row) {
        $links[] = array("source" => $nodeNum[$row["prerequisite"]], "target" => $nodeNum[$row["topic"]], "value" => 1);
    }
    
    $query = "SELECT id, username FROM user WHERE user_type = 0";
    $results = mysqli_query($db, $query);
    $students = mysqli_fetch_all($results, MYSQLI_ASSOC);
?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Home</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link href="home.css" rel="stylesheet">


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link href="../../node_modules/slim-select/dist/slimselect.css" rel="stylesheet">
    <script src="../../node_modules/slim-select/dist/slimselect.js"></script>
	<style>
		tr[data-href]{
			cursor: pointer;
		}
	</style>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>



    
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

    <div class="top-bar-right">
        
        <ul class="navbar-nav mr-auto">
            <li><input type="text" id="topicInput" list="topicList" placeholder="Search for topics.."></li>
            <datalist id="topicList">
                <?php
                    foreach ($topics as $row) {
                ?>
                <option value="<?php echo $row["name"]; ?>"></option>
                <?php
                    }
                ?>
            </select>
        </ul>
        
        <ul class="navbar-nav mr-auto">
            <li><input type="text" id="studentInput" list="studentList" placeholder="Check student progress"></li>
            <datalist id="studentList">
                <?php
                    foreach ($students as $row) {
                ?>
                <option value="<?php echo $row["username"]; ?>" data-value="<?php echo $row["id"]; ?>"></option>
                <?php
                    }
                ?>
            </select>
        </ul>
    </div>

     
        <!-- create sub topic -->
        <form action="../topic/topic_handler.php" method="post">
		<div class="modal fade" id="courseAddModal" tabindex="-1" role="dialog" aria-labelledby="courseAddModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="courseAddModalLabel">Create new topic</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form>
					<div class="form-group">
						<label class="col-form-label">Topic:</label>
						<input name="function" value="createTopic" hidden>
						<input name="name" type="text" class="form-control">
					</div>
					<div class="form-group">
						<label for="message-text" class="col-form-label">Description:</label>
						<textarea class="form-control" id="message-text" name="description"></textarea>
					</div>
					<div class="form-group">
						<label for="message-text" class="col-form-label">Prerequisite:</label>
                        <select id="prerequisite" name="prerequisite[]" multiple>
                            <?php
                                $query = "SELECT id, name FROM topics ORDER BY name";
                                $results = mysqli_query($db, $query);
                                foreach (mysqli_fetch_all($results, MYSQLI_ASSOC) as $row) {
                            ?>
                            <option value="<?php echo $row["id"]; ?>"><?php echo $row["name"]; ?></option>
                            <?php
                                }
                            ?>
                        </select>
					</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary" value="Create">Submit</button>
				</div>
				</div>
			</div>
		</div>
		<button class="plus-button" data-toggle="modal" data-target="#courseAddModal" data-whatever="@mdo"></button>

	</form>
	</nav>

    <div id="main">
        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <div id="sideNavContent"></div>
            <div id="topicProgress"></div>
        </div> 
    </div>

	<script src="https://dagrejs.github.io/project/dagre-d3/latest/dagre-d3.js"></script>

</body>

<script>
    var studentID = 0;
    var topicID = 0;

    width = 850,
    height = 600,
    r = 12,
    gravity = 0.1,
    distance = 100,
    charge = -800,
    fill = d3.scale.category10(),
    nodes=<?php echo json_encode($nodes); ?>,
    links=<?php echo json_encode($links); ?>;

    var svg = d3.select("body").append("svg")
 
        svg = d3.select("#main").append("svg")
        .attr("width", width)
        .attr("height", height);

    var force = d3.layout.force()
       
        .distance(distance)
        .charge(charge)
        .size([width, height]);

    force.nodes(nodes)
        .links(links)
        .start();

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
        
        
    var div = d3.select("main").append("div")	
        .attr("class", "tooltip")				
        .style("opacity", 0);

    var circle=node.append("svg:circle").attr("r", r - .75).style("fill", "#4287f5"
        ).style("stroke", d3.rgb("#4287f5").darker()
        ).call(force.drag)
        .on("click", function(d) {
            openNav(d.id);
        });

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

    var path = svg.append("svg:g").selectAll("path")
        .data(force.links())
        .enter().append("svg:path")
        .attr("class", function(d) { return "link " + d.value; })
        .attr("marker-end", function(d) { return "url(#" + d.value + ")"; });

    var text = svg.append("svg:g").selectAll("g")
        .data(force.nodes())
        .enter().append("svg:g");

    text.append("svg:text")
          .attr("dx", 12)
          .attr("dy", ".35em")
          .attr("class", "shadow")
          .text(function(d) { return d.name;}
      );

    text.append("svg:text")
          .attr("dx", 12)
          .attr("dy", ".35em")
          .text(function(d) { return d.name;}
      );

    force.on("tick", tick);

    // move circles using force
    function tick() {
        path.attr("d", function(d) {
            var dx = d.target.x - d.source.x,
                dy = d.target.y - d.source.y,
                dr = 0;
            return "M" + d.source.x + "," + d.source.y + "A" + dr + "," + dr + " 0 0,1 " + d.target.x + "," + d.target.y;
        });

        circle.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });

        text.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
    };
    

	$('#exampleModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget) // Button that triggered the modal
		var recipient = button.data('whatever') // Extract info from data-* attributes
		// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		var modal = $(this)
		modal.find('.modal-title').text('New message to ' + recipient)
		modal.find('.modal-body input').val(recipient)
	});

    
    $("#topicInput").bind('input', function () {
        $.ajax({
            url: "../topic/topic_handler.php",
            method: "POST",
            data: "function=searchTopic&name=" + $('#topicInput').val(),
            success: function(result){
                if (result != "") {
                    window.location = result;
                }
            }
        });
    });
    
    function progressColour(d, progressList) {
        var id = parseInt(d.id);
        var colour = "#4287f5";
        $.each(progressList, function (i, obj) {
            if (id == obj['id'] && obj['progress'] == obj['nSub']) {
                colour = "#c0c6cf"; 
            }
        });
        return colour;
    };
    
    $("#studentInput").bind('input', function () {
        var id = $('#studentList option[value="' + $('#studentInput').val() + '"]').data('value');
        $.ajax({
            url: "../topic/topic_handler.php",
            method: "POST",
            data: "function=searchProgress&student=" + id,
            success: function(result){
                if (result == "") {
                    studentID = 0;
                    circle.transition().duration(500).style("fill", "#4287f5"
                    ).style("stroke", d3.rgb("#4287f5").darker());
                } else {
                    studentID = id;
                    var progressList = JSON.parse(result);
                    circle.transition().duration(500).style("fill", function (d) {
                        return progressColour(d, progressList); 
                    }).style("stroke", function (d) {
                        return d3.rgb(progressColour(d, progressList)).darker(); 
                    });
                }
                showProgressBar();
            }
        });
    });

    
    function openNav(id) {
        topicID = id;
        $.ajax({
            url: "../topic/topic_handler.php",
            method: "POST",
            data: "function=getInfo&id=" + id,
            success: function(result){
                $("#sideNavContent").html(result);
                document.getElementById("mySidenav").style.width = "550px";
                document.getElementById("main").style.marginLeft = "550px";
            }
        });
        
        showProgressBar();
    }
    
    function closeNav() {
        topicID = 0;
        document.getElementById("mySidenav").style.width = "0px";
        document.getElementById("main").style.marginLeft= "0";
    }

    
    function showProgressBar() {
        if (studentID == 0 || topicID == 0) {
            $("#topicProgress").html("");
        } else {
            $.ajax({
                url: "../topic/topic_handler.php",
                method: "POST",
                data: "function=progressBar&student=" + studentID + "&topic=" + topicID,
                success: function(result){
                    $("#topicProgress").html(result);
                }
            });
        }
    }

    $(document).ready(function () {
        var instance = new SlimSelect({
            select: '#prerequisite'

        });
    });
</script>

</html>

