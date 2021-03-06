<?php 
include('server.php');
include('../../checks.php');

if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: auth/login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<?php
    $nodes = array();
    $nodeNum = array();
    $links = array();
    $i = 0;
    
    $query = "SELECT id, name FROM topics";
    $results = mysqli_query($db, $query);
    $topics = mysqli_fetch_all($results, MYSQLI_ASSOC);
    $topicCat = array();
    foreach ($topics as $row) {
        $id = $row["id"];
        $nodes[] = array("name" => $row["name"], "id" => $id);
        $nodeNum[$id] = $i;
        $i ++;
        
        $topicCat[$id] = array();
    }
    
    $query = "SELECT topic, category FROM topic_categories";
    $results = mysqli_query($db, $query);
    $rows = mysqli_fetch_all($results, MYSQLI_ASSOC);
    foreach ($rows as $row) {
        $topicCat[$row['topic']][] = $row['category'];
    }
    
    $query = "SELECT id, name FROM categories ORDER BY name";
    $results = mysqli_query($db, $query);
    $categories = mysqli_fetch_all($results, MYSQLI_ASSOC);
    
    $query = "SELECT topic, prerequisite FROM prerequisites";
    $results = mysqli_query($db, $query);
    $rows = mysqli_fetch_all($results, MYSQLI_ASSOC);
    foreach ($rows as $row) {
        $links[] = array("source" => $nodeNum[$row["prerequisite"]], "target" => $nodeNum[$row["topic"]], "value" => 1);
    }
    
    if (permission()) {
        $progresses = array();
    } else {
        $progresses = getStudentProgresses($_SESSION['user']["id"], $db);
    }
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
    <script src="https://d3js.org/d3.v4.min.js"></script>
</head>

<body>
    <div class="top-bar-right">
        <a href="home.php">
			<img src="img/unsw_0.png" href="home.php">
		</a>
        

        <input type="text" id="topicInput" placeholder="Search for topics..">
        <datalist id="topicList">
            <?php
                foreach ($topics as $row) {
            ?>
            <option value="<?php echo $row["name"]; ?>" data-value="<?php echo $row["id"]; ?>"></option>
            <?php
                }
            ?>
        </datalist>
            
        <?php
            if (permission()) {
                $query = "SELECT id, username FROM user WHERE user_type = 0";
                $results = mysqli_query($db, $query);
                $students = mysqli_fetch_all($results, MYSQLI_ASSOC);
        ?>
        
        <input type="text" id="studentInput" list="studentList" placeholder="Check student progress">
        <datalist id="studentList">
            <?php
                foreach ($students as $row) {
            ?>
            <option value="<?php echo $row["username"]; ?>" data-value="<?php echo $row["id"]; ?>"></option>
            <?php
                }
            ?>
        </datalist>

        
        
        <?php
            }
        ?>



        <div class="btn-group">
            <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Select category
            </button>
            <div class="dropdown-menu" id="categoryList">
                <?php
                    foreach ($categories as $cat) {
                ?>
                <a class="dropdown-item">
                    <input type="checkbox" name="category" value="<?php echo $cat['id']; ?>"/>
                    <span id="catName_<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></span>
                </a>
                <?php
                    }
                ?>
            </div>
        </div>

        <?php
            if (permission()) {
        ?>
        <button class="plus-button" data-toggle="modal" data-target="#courseAddModal" data-whatever="@mdo"></button>
            
        <?php
            }
        ?>



        
		<a class="btn btn-secondary" id="nav-link" href="login.php?logout='1'">Logout</a>	<?php echo display_error(); ?>
  


    </div>

    <?php
        if (permission()) {
    ?>
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
						<label for="message-text" class="col-form-label">Category:</label>
                        <select id="category" name="category[]" multiple>
                            <?php
                                $query = "SELECT id, name FROM categories ORDER BY name";
                                $results = mysqli_query($db, $query);
                                foreach (mysqli_fetch_all($results, MYSQLI_ASSOC) as $row) {
                            ?>
                            <option value="<?php echo $row["id"]; ?>"><?php echo $row["name"]; ?></option>
                            <?php
                                }
                            ?>
                        </select>
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
		

        </form>
        
   
        
    <?php
        }
    ?>
	</nav>

    
    <div class="alertt">
        <?php echo display_error(); ?>
        <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif ?>
    </div>
    
    <div id="main">
    
        <div id="mySidenav" class="sidenav">
            <div id="sideNavContent"></div>
        </div> 
        <div id="topicTree"></div>
    </div>

	<script src="https://dagrejs.github.io/project/dagre-d3/latest/dagre-d3.js"></script>

</body>

<script>
    var initWidth = $("body").prop("clientWidth") - 20;
    
    var selectedTopic = <?php echo isset($_GET['topic']) ? $_GET['topic'] : 0; ?>,
        defaultX = 0,
        defaultY = 0,
        defaultScale = 1,
        currX = 0,
        currY = 0,
        isStudent = <?php echo permission() ? "false" : "true"; ?>;

    var width = initWidth,
        height = 620,
        r = 18,
        nodes=<?php echo json_encode($nodes); ?>,
        links=<?php echo json_encode($links); ?>,
        progresses=<?php echo json_encode($progresses); ?>,
        topicCat=<?php echo json_encode($topicCat); ?>,
        catChecked=[];
    
    var simulation = d3.forceSimulation(nodes)
        .force("charge", d3.forceManyBody().strength(-1000))
        .force("link", d3.forceLink(links).distance(100).strength(2).iterations(10))
        .force("x", d3.forceX())
        .force("y", d3.forceY())
        .stop();
        
    var svg = d3.select("#topicTree").append("svg")
        .attr("width", width)
        .attr("height", height);
    
    var container = svg.append("g")
        .attr("width", width)
        .attr("height", height);

    var g = container.append("g").attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

    var n = <?php echo count($nodes); ?>;
        
    var path, circle, text, text_shadow, groupText, groupShadow;
        
    var nodeStatus = d3.select("body").append("div")
        .attr("class", "nodeStatus")
        .style("opacity", 0);

    d3.timeout(function() {
        for (var i = 0, n = Math.ceil(Math.log(simulation.alphaMin()) / Math.log(1 - simulation.alphaDecay())); i < n; ++i) {
            simulation.tick();
        }
        
        g.append("defs").selectAll("marker")
            .data(nodes)
        .enter().append("marker")
            .attr("id", function(d) {return d.id;})
            .attr("viewBox", "0 -5 10 10")
            .attr("refX", 30)
            .attr("refY", 0)
            .attr("markerWidth", 6)
            .attr("markerHeight", 6)
            .attr("fill-color","#cccccc")
            .attr("orient", "auto")
        .append("path")
            .attr("d", "M0,-5L10,0L0,5");

        path = g.append("g")
            .attr("stroke", "#000")
            .attr("stroke-width", 1.5)
            .selectAll("path")
            .data(links)
        .enter().append("line")
            .attr("id", function(d) { return "path_" + d.source.id + "_" + d.target.id; })
            .attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; })
            .attr("marker-end", function(d) { return "url(#" + d.target.id + ")"; });
            
        circle = g.append("g")
            .attr("stroke", "#fff")
            .attr("stroke-width", 1.5)
            .selectAll("circle")
            .data(nodes)
        .enter().append("circle")
            .attr("id", function(d) { return "circle_" + d.id; })
            .attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; })
            .attr("r", r - .75)
        .style("fill", function (d) {
            return progressColour(d.id, false); 
        }).style("stroke", function (d) {
            return progressColour(d.id, true);
        }).on("click", function(d) {
            openNav(d.id);
        }).on("mouseover", function(d) {
            circleOnMouseOver(d.id);
        }).on("mouseout", function(d) {
            nodeStatus.transition()		
                .duration(500)		
                .style("opacity", 0);	
        });
    
        text_shadow = g.append("g").selectAll("circle")
            .data(nodes)
        .enter().append("text")
            .attr("id", function(d) { return "shadow_" + d.id; })
            .attr("x", function(d) { return d.x; })
            .attr("y", function(d) { return d.y + r + 6; })
            .attr("dy", ".35em")
            .attr("class", "shadow")
            .style("text-anchor", "middle")
            .text(function(d) { return d.name;});

        text = g.append("g").selectAll("circle")
            .data(nodes)
        .enter().append("text")
            .attr("id", function(d) { return "text_" + d.id; })
            .attr("x", function(d) { return d.x; })
            .attr("y", function(d) { return d.y + r + 6; })
            .attr("dy", ".35em")
            .style("text-anchor", "middle")
            .text(function(d) { return d.name;});
            
        // display sidenav if a topic is selected on HTTP GET
        if (selectedTopic != 0) {
            openNav(selectedTopic);
        }
        
        // display main topic in a node cluster when zooming out enough
        var nodeGroups = [];
        var nodesQueue = [];
        var j = 0;
        for (var i = 0; i < nodes.length; i++) {
            var d = nodes[i];
            if ($.inArray(d, nodesQueue) < 0) {
                nodesQueue.push(d);
                var group = [];
                while (j < nodesQueue.length) {
                    var node = nodesQueue[j];
                    var count = 0;
                    $(links).each(function (_, l) {
                        if (l.source == node || l.target == node) {
                            count++;
                            var anotherNode = l.source == node ? l.target : l.source;
                            if ($.inArray(anotherNode, nodesQueue) < 0) {
                                nodesQueue.push(anotherNode);
                            }
                        }
                    });
                    group.push([node, count]);
                    j++;
                }
                nodeGroups.push(group);
            }
        }
        
        var bigText = [];
        $(nodeGroups).each(function (_, group) {
            var xs = [],
                ys = [],
                bestNode = group[0];
            $(group).each(function (_, node) {
                xs.push(node[0].x);
                ys.push(node[0].y);
                if (node[1] > bestNode[1]) {
                    bestNode = node;
                }
            });
            bigText.push({
                x: (Math.min.apply(Math, xs) + Math.max.apply(Math, xs))/2,
                y: (Math.min.apply(Math, ys) + Math.max.apply(Math, ys))/2,
                name: bestNode[0].name
            });
        });
        
        groupShadow = g.append("g").selectAll("circle")
            .data(bigText)
        .enter().append("text")
            .attr("x", function(d) { return d.x; })
            .attr("y", function(d) { return d.y; })
            .attr("class", "shadow")
            .style("text-anchor", "middle")
            .style("font-size", 50)
            .text(function(d) { return d.name;});

        groupText = g.append("g").selectAll("circle")
            .data(bigText)
        .enter().append("text")
            .attr("x", function(d) { return d.x; })
            .attr("y", function(d) { return d.y; })
            .style("text-anchor", "middle")
            .style("font-size", 50)
            .text(function(d) { return d.name;});
        
        svg.call(d3.zoom().on("zoom", function () {
            container.attr("transform", d3.event.transform);
            svgOpacity(d3.event.transform.k);
        }));
        
        // set zooming size to display all nodes
        defaultZoomSize();
    });
    
    function calculateZoomSize(xs, ys) {
        if (xs.length > 0) {
            var w = $("svg").attr("width");
            var xMin = Math.min.apply(Math, xs) - 2*r;
            var xMax = Math.max.apply(Math, xs) + 2*r;
            var yMin = Math.min.apply(Math, ys) - 2*r;
            var yMax = Math.max.apply(Math, ys) + 2*r;
            var xScale = w/(xMax - xMin);
            var yScale = height/(yMax - yMin);
            
            defaultScale = xScale < yScale ? xScale : yScale;
            var m = (.5 - 1 / (2 * defaultScale));
            defaultX = -(xMax + xMin)/2 - m * w;
            defaultY = -(yMax + yMin)/2 - m * height;
            svgTransform(defaultScale, defaultX + (w - initWidth)/2, defaultY);
        }
    }
    
    function defaultZoomSize() {
        var xs = [],
            ys = [];
        $(nodes).each(function (_, d) {
            xs.push(d.x);
            ys.push(d.y);
        });
        calculateZoomSize(xs, ys);
    }
    
    function circleOnMouseOver(id) {
        if (isStudent) {
            nodeStatus.transition()		
                .duration(200)		
                .style("opacity", .9);		
            nodeStatus.html(nodeStatusMessage(id))	
                .style("left", (event.pageX) + "px")		
                .style("top", (event.pageY - 28) + "px");
        }
    }

	$('#exampleModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget) // Button that triggered the modal
		var recipient = button.data('whatever') // Extract info from data-* attributes
		// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		var modal = $(this)
		modal.find('.modal-title').text('New message to ' + recipient)
		modal.find('.modal-body input').val(recipient)
	});
    
    $("#topicInput").bind('input', function () {
        var input = $('#topicInput').val();
        if (input != "") {
            if ($('#searchNotFound').length) {
                $('#searchNotFound').remove();
            }
            $(this).attr("list", "topicList");
            
            var inputUC = input.toUpperCase(),
                id = 0,
                found = false;
            $('#topicList option').each(function () {
                var val = $(this).val().toUpperCase();
                if (val.includes(inputUC)) {
                    found = true;
                    if (val == inputUC) {
                        id = $(this).data('value');
                    }
                }
            });
        
            if (id != 0) {
                openNav(id);
            }
            if (!found) {
                $('.alertt').append('<div id="searchNotFound" class="alert alert-warning alert-dismissible fade show" role="alert">No topic name matches with "'
                    + input + '"<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            }
        } else {
            $(this).attr("list", "");
        }
    });
    
    const defaultColour = "#11d975",
        unavailableColour = "#c0c6cf",
        nextColour = "#4287f5",
        selectedColour = "#ff0000";
        
    const inProgress = g.append("defs")
    .append("linearGradient")
        .attr("id", "inProgress")
        .attr("x1", "0%")
        .attr("x2", "0%")
        .attr("y1", "100%")
        .attr("y2", "0%");
    inProgress.append("stop")
        .attr("offset", "50%")
        .attr("stop-color", defaultColour);
    inProgress.append("stop")
        .attr("offset", "50%")
        .attr("stop-color", nextColour);
        
    function nodeStatusMessage(id) {
        switch (progressColour(id, false)) {
            case unavailableColour:
                return "Prerequisite not completed";
            case nextColour:
                return "Available to study";
            case "url(#inProgress)":
                return "In progress";
            default:
                return "Completed";
        }
    };
    
    function progressColour(id, stroke) {
        var colour = defaultColour;
        if (id == selectedTopic && stroke) {
            colour = selectedColour;
        } else {
            if (isStudent) {
                colour = unavailableColour;
                $.each(progresses, function (_, obj) {
                    if (id == obj.id) {
                        if (obj.progress == obj.nSub) {
                            colour = defaultColour;
                        } else if (obj.progress > 0) {
                            colour = stroke ? "#27e3da" : "url(#inProgress)";
                        }
                    }
                });
                
                if (colour == unavailableColour) {
                    var initial = true;
                    $.each(links, function (_, l) {
                        if (id == l.target.id) {
                            initial = false;
                            if (progressColour(l.source.id, false) == defaultColour) {
                                colour = nextColour;
                            }
                        }
                    });
                    if (initial) {
                        colour = nextColour;
                    }
                }
            }
            if (stroke) {
                colour = d3.rgb(colour).darker();
            }
        }
        return colour;
    };
    
    <?php
        if (permission()) {
    ?>
    $("#studentInput").bind('input', function () {
        var input = $('#studentInput').val(),
            id = 0;
        if (input != "") {
            if ($('#searchNotFound').length) {
                $('#searchNotFound').remove();
            }
            $(this).attr("list", "studentList");
            
            var inputUC = input.toUpperCase(),
                found = false;
            $('#studentList option').each(function () {
                var val = $(this).val().toUpperCase();
                if (val.includes(inputUC)) {
                    found = true;
                    if (val == inputUC) {
                        id = $(this).data('value');
                    }
                }
            });
            
            if (!found) {
                $('.alertt').append('<div id="searchNotFound" class="alert alert-warning alert-dismissible fade show" role="alert">No student name matches with "'
                    + input + '"<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            }
        } else {
            $(this).attr("list", "");
        }
        
        if ($('#viewAsStudent').length) {
            $('#viewAsStudent').remove();
        }
        if (id != 0) {
            $('.alertt').append('<div id="viewAsStudent" class="alert alert-info alert-dismissible fade show" role="alert">You are viewing the topic tree as <b>'
                + input + '</b><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            $.ajax({
                url: "../topic/topic_handler.php",
                method: "POST",
                data: "function=searchProgress&student=" + id,
                success: function(result){
                    progresses = JSON.parse(result);
                    isStudent = true;
                    circle.transition().duration(500).style("fill", function (d) {
                        return progressColour(d.id, false); 
                    }).style("stroke", function (d) {
                        return progressColour(d.id, true); 
                    });
                }
            });
        } else {
            progresses = [];
            isStudent = false;
            circle.transition().duration(500).style("fill", function (d) {
                return progressColour(d.id, false); 
            }).style("stroke", function (d) {
                return progressColour(d.id, true); 
            });
        }
    });

    $(document).ready(function () {
        var instance = new SlimSelect({
            select: '#prerequisite'
        });
        var instance2 = new SlimSelect({
            select: '#category'
        });
    });
    <?php
        }
    ?>
    
    function openNav(id) {
        selectedTopic = id;
        var topicStr = selectedTopic.toString();
        $.each(nodes, function (i, obj) {
            if (selectedTopic == obj['id']) {
                currX = obj['x'];
                currY = obj['y'];
            }
        });
        
        $("line").each(function (){
            var line = $(this);
            if (line.attr("opacity") == 0) {
                var lid = line.attr("id").split("_");
                if (lid[1] == topicStr || lid[2] == topicStr) {
                    var anotherC = lid[1] == topicStr ? lid[2] : lid[1];
                    if ($("#circle_" + anotherC).attr("opacity") > 0) {
                        line.attr("opacity", 0.1);
                        line.addClass("transparent");
                    }
                }
            }
        });
        
        var cir = $("#circle_" + selectedTopic);
        if (cir.attr("opacity") == 0) {
            cir.attr("opacity", 0.1)
            .addClass("transparent")
            .on("click", function() {
                openNav(id);
            }).on("mouseover", function(event) {
                if (isStudent) {
                    circleOnMouseOver(id);
                    nodeStatus.transition()		
                        .duration(200)		
                        .style("opacity", .9);		
                    nodeStatus.html(nodeStatusMessage(id))	
                        .style("left", (event.pageX) + "px")		
                        .style("top", (event.pageY - 28) + "px");
                }
            });
        
            $("#text_" + selectedTopic).attr("opacity", 0.1);
            $("#shadow_" + selectedTopic).attr("opacity", 0.1);
        }

        $.ajax({
            url: "../topic/topic_handler.php",
            method: "POST",
            data: "function=getInfo&id=" + selectedTopic,
            success: function(result){
                $("#sideNavContent").html(result);
                if ($("#mySidenav").css("display") == "none") {
                    $('#mySidenav').show();
                }
                resizeSvgAndSidebar(true);
                circle.style("stroke", function (d) {
                    return progressColour(d.id, true);
                });
            }
        });
    }
    
    $(window).resize(function () {
        width = $("body").prop("clientWidth") - 20;
        resizeSvgAndSidebar(false);
    });
    
    function svgTransform(scale, x, y) {
        svgOpacity(scale);
        
        container.transition()
            .duration(750)
            .attr("transform", "scale(" + scale + ") translate(" + x + "," + y + ")");
        svg.call(d3.zoom().transform, d3.zoomIdentity.scale(scale).translate(x, y));
    }
    
    function svgOpacity(scale) {
        var normalOpacity = scale < 0.3 ? 0.2 : 1;
        var groupOpacity = scale < 0.3 ? 1 : 0;
        
        if (catChecked.length > 0) {
            $("circle").each(function () {
                if (!$(this).hasClass("transparent")) {
                    var id = $(this).attr("id").split("_")[1];
                    if (topicCat[id].length == 0 || $(topicCat[id]).filter(catChecked).length > 0) {
                        $(this).attr("opacity", normalOpacity)
                        .on("click", function() {
                            openNav(id);
                        }).on("mouseover", function() {
                            circleOnMouseOver(id);
                        });
                        $("#text_" + id).attr("opacity", normalOpacity);
                        $("#shadow_" + id).attr("opacity", normalOpacity);
                    } else {
                        $(this).attr("opacity", 0)
                        .on("click", function() {
                            void 0;
                        }).on("mouseover", function() {
                            void 0;
                        });
                        $("#text_" + id).attr("opacity", 0);
                        $("#shadow_" + id).attr("opacity", 0);
                    }
                }
            });
            $("line").each(function () {
                if (!$(this).hasClass("transparent")) {
                    var lid = $(this).attr("id").split("_");
                    var src = lid[1];
                    var tar = lid[2];
                    $(this).attr("opacity", function (d) {
                        return ((topicCat[src].length == 0 || $(topicCat[src]).filter(catChecked).length > 0) && (topicCat[tar].length == 0 || $(topicCat[tar]).filter(catChecked).length > 0)) ? normalOpacity : 0;
                    });
                }
            });
        } else {
            circle.attr("opacity", normalOpacity)
            .on("click", function(d) {
                openNav(d.id);
            }).on("mouseover", function(d) {
                circleOnMouseOver(d.id);
            });
            path.attr("opacity", normalOpacity);
            text.attr("opacity", normalOpacity);
            text_shadow.attr("opacity", normalOpacity);
        }
        groupText.attr("opacity", groupOpacity);
        groupShadow.attr("opacity", groupOpacity);
    }
    
    function resizeSvgAndSidebar(transform) {
        var w = width,
            scale = defaultScale,
            x = w == initWidth ? defaultX : defaultX + (w - initWidth)/2,
            y = defaultY;
        if ($("#mySidenav").css("display") == "none") {
            document.getElementById("mySidenav").style.width = "0px";
        } else {
            if (window.innerWidth > 1000) {
                w -= 700;
                document.getElementById("mySidenav").style.width = "630px";
            } else {
                document.getElementById("mySidenav").style.width = "100%";
            }
            scale = 2;
            x = -((2 * initWidth - w)/4 + currX);
            y = -(height/4 + currY);
        }
        svg.attr("width", w);
        container.attr("width", w);
        
        if (transform) {
            svgTransform(scale, x, y);
        }
    }
    
    $('svg').click(function (event) {
        if (event.target.nodeName != "circle" && !sideNavClicked($(event.target))) {
            $('#mySidenav').hide();
            resizeSvgAndSidebar(true);
            currX = 0;
            currY = 0;
            selectedTopic = 0;
            circle.style("stroke", function (d) {
                return progressColour(d.id, true);
            });
        }
    });
    
    function sideNavClicked(element) {
        if (element.attr("id") == "mySidenav") {
            return true;
        }
        var parents = element.parents().map(function () {
            return $(this).attr("id") == "mySidenav";
        });
        return $.inArray(true, parents) >= 0;
    }
    
    function filterTopicTree() {
        if ($("#mySidenav").css("display") == "block") {
            $('#mySidenav').hide();
            resizeSvgAndSidebar(false);
            currX = 0;
            currY = 0;
            selectedTopic = 0;
            circle.style("stroke", function (d) {
                return progressColour(d.id, true);
            });
        }
        if ($('#topicsFiltered').length) {
            $('#topicsFiltered').remove();
        }
        $("circle").each(function () {
            $(this).removeClass("transparent");
        });
        $("line").each(function (){
            $(this).removeClass("transparent");
        });
        
        if (catChecked.length > 0) {
            var xs = [], ys = [];
            $(nodes).each(function (_, d) {
                if (topicCat[d.id].length == 0 || $(topicCat[d.id]).filter(catChecked).length > 0) {
                    xs.push(d.x);
                    ys.push(d.y);
                }
            });
            
            var catNames = "";
            for (var i = 0; i < catChecked.length; i++) {
                if (i > 0) {
                    catNames += i == catChecked.length - 1 ? " and " : ", ";
                }
                catNames += "<b>" + $('#catName_' + catChecked[i]).text() + "</b>";
            };
            
            if (xs.length > 0) {
                var message = 'You have selected ';
                calculateZoomSize(xs, ys);
            } else {
                catChecked = [];
                var message = 'No topics found in ';
                defaultZoomSize();
            }
            $('.alertt').append('<div id="topicsFiltered" class="alert alert-info alert-dismissible fade show" role="alert">' + message
                + catNames + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            defaultZoomSize();
        }
    };
    
    function categoryButtonClicked(id) {
        catChecked = [id.toString()];
        $('input[name="category"]').each(function() {
            $(this).prop('checked', $(this).val() == id);
        });
        
        filterTopicTree();
    };
    
    $('input[name="category"]').change(function () {
        catChecked = [];
        $('input[name="category"]:checked').each(function() {
            catChecked.push($(this).val());
        });
        
        filterTopicTree();
    });

    if (isStudent) {
        $('[data-toggle="tooltip"]').tooltip();
    };

    
</script>

</html>

