<?php 
include "session.inc";
error_reporting(E_ALL);
include "header.inc";
$parms = @trim(strip_tags($_GET['nodes']));
$passedNodes = explode(',', @trim(strip_tags($_GET['nodes'])));
#print_r($nodes);

if (count($passedNodes) == 0) {
    die ("Please provide a properly formated URI. (ie link.php?nodes=1234 | link.php?nodes=1234,2345)");
}

// Get Allstar database file
$db = "astdb.txt";
$astdb = array();
if (file_exists($db)) {
    $fh = fopen($db, "r");
    if (flock($fh, LOCK_SH)){
        while(($line = fgets($fh)) !== FALSE) {
            $arr = preg_split("/\|/", trim($line));
            $astdb[$arr[0]] = $arr;
        }
    }
    flock($fh, LOCK_UN);
    fclose($fh);
    #print "<pre>"; print_r($astdb); print "</pre>";
}

// Read allmon INI file
if (!file_exists('allmon.ini.php')) {
    die("Couldn't load allmon ini file.\n");
}
$config = parse_ini_file('allmon.ini.php', true);
#print "<pre>"; print_r($config); print "</pre>";
#print "<pre>"; print_r($config[$node]); print "</pre>";

// Remove nodes not in our allmon.ini file.
$nodes=array();
foreach ($passedNodes as $i => $node) {
	if (isset($config[$node])) {
		$nodes[] = $node;
	} else {
		print "Warning: Node $node not found in our allmon ini file.";
	}
}

?>
<script type="text/javascript">
// when DOM is ready
$(document).ready(function() {
    if(typeof(EventSource)!=="undefined") {
		
		// Start SSE 
        var source=new EventSource("server.php?nodes=<?php echo $parms; ?>");
        
        // Fires when node data come in. Updates the whole table
        source.addEventListener('nodes', function(event) {
			//console.log('nodes: ' + event.data);	        
            // server.php returns a json formated string
            var tabledata = JSON.parse(event.data);
        	for (var localNode in tabledata) {
        		var tablehtml = '';
				if (tabledata[localNode].remote_nodes.length == 0) {
					$('#table_' + localNode  + ' tbody:first').html('<tr><td colspan="7">No connections.</td></tr>');
				} else {
	        		for (row in tabledata[localNode].remote_nodes) {
        		
	        			// Set green, red or no background color 
	            		if (tabledata[localNode].remote_nodes[row].keyed == 'yes') {
		            		tablehtml += '<tr class="rColor">';
	            		} else if (tabledata[localNode].remote_nodes[row].mode == 'C') {
		            		tablehtml += '<tr class="cColor">';
	            		} else {
		            		tablehtml += '<tr>';
	            		}
	            		var id = 't' + localNode + 'c0' + 'r' + row;
	            		//console.log(id);
	            		//tablehtml += '<td id="' + id + '" class="nodeNum">' + tabledata[localNode].remote_nodes[row].node + '</td>';
	            		tablehtml += '<td id="' + id + '">' + tabledata[localNode].remote_nodes[row].node + '</td>';
            		
	            		// Show info or IP if no info
	            		if (tabledata[localNode].remote_nodes[row].info != "") {
		            		tablehtml += '<td>' + tabledata[localNode].remote_nodes[row].info + '</td>';
	            		} else {
		            		tablehtml += '<td>' + tabledata[localNode].remote_nodes[row].ip + '</td>';
	            		}
            		
	            		tablehtml += '<td><span id="lkey' + row + '">' + tabledata[localNode].remote_nodes[row].last_keyed + '</span></td>';
	            		tablehtml += '<td>' + tabledata[localNode].remote_nodes[row].link + '</td>';
	            		tablehtml += '<td>' + tabledata[localNode].remote_nodes[row].direction + '</td>';
	            		tablehtml += '<td><span id="elap' + row +'">' + tabledata[localNode].remote_nodes[row].elapsed + '</span></td>';
            		
	            		// Show mode in plain english
	            		if (tabledata[localNode].remote_nodes[row].mode == 'R') {
		            		tablehtml += '<td>RX only</td>';
	            		} else if (tabledata[localNode].remote_nodes[row].mode == 'T') {
		            		tablehtml += '<td>Transceive</td>';
		            	} else if (tabledata[localNode].remote_nodes[row].mode == 'C') {
		            		tablehtml += '<td>Connecting</td>';
	            		} else {
		            		tablehtml += '<td>' + tabledata[localNode].remote_nodes[row].mode + '</td>';
	            		}
                  
                  tablehtml += '<td>';
                  // Disconnect button
                  tablehtml += '<button type="button" class="disconnect-button btn btn-danger btn-sm" id="disconnect-button_' + localNode + '_' + tabledata[localNode].remote_nodes[row].node + '">Disconnect</button>';
	            		
                  tablehtml += '</td></tr>';
	        		}
        		
					//console.log('tablehtml: ' + tablehtml);
	        		$('#table_' + localNode + ' tbody:first').html(tablehtml);
				}
        	}
        });
        
        // Fires when new time data comes in. Updates only time columns
        source.addEventListener('nodetimes', function(event) {
			//console.log('nodetimes: ' + event.data);	        
			var tabledata = JSON.parse(event.data);
			for (localNode in tabledata) {
				tableID = 'table_' + localNode;
				for (row in tabledata[localNode].remote_nodes) {
					//console.log(tableID, row, tabledata[localNode].remote_nodes[row].elapsed, tabledata[localNode].remote_nodes[row].last_keyed);
					rowID='lkey' + row;
					$( '#' + tableID + ' #' + rowID).text( tabledata[localNode].remote_nodes[row].last_keyed );
					rowID='elap' + row;
					$( '#' + tableID + ' #' + rowID).text( tabledata[localNode].remote_nodes[row].elapsed );
				}
			}
			
			if (blinky == "*") {
				blinky = "&nbsp;";
			} else {
				blinky = "*";
			}
			$('#blinky').html(blinky);
        });
        
        // Fires when conncetion message comes in.
        source.addEventListener('connection', function(event) {
			//console.log(statusdata.status);
			var statusdata = JSON.parse(event.data);
			tableID = 'table_' + statusdata.node;
			$('#' + tableID + ' tbody:first').html('<tr><td colspan="7">' + statusdata.status + '</td></tr>');
		});
		       
    } else {
        $("#list_link").html("Sorry, your browser does not support server-sent events...");
    }
});
</script>
<br/>
<!-- Connect form -->
<div id="connect_form">
<?php /*
if (count($nodes) > 0) {
    if (count($nodes) > 1) {
        print "<select id=\"localnode\">";
        foreach ($nodes as $node) {
		if (isset($astdb[$node]))
			$info = $astdb[$node][1] . ' ' . $astdb[$node][2] . ' ' . $astdb[$node][3];
		else
			$info = "Node not in database";
            print "<option value=\"$node\">$node - $info</option>";
        }
        print "</select>\n";
    } else {
        print "<input type=\"hidden\" id=\"localnode\" value=\"{$nodes[0]}\">\n";
    } */
?>
<!--
<input type="text" id="node">
Permanent <input type="checkbox"><br/>
<input type="button" value="Connect" id="connect">
<input type="button" value="Disconnect" id="disconnect">
<input type="button" value="Monitor" id="monitor">
<input type="button" value="Local Monitor" id="localmonitor">
<input type="button" value="Control Panel" id="controlpanel">
-->
<?php
//} #endif (count($nodes) > 0)
?>
</div>

<!-- Nodes table -->
<div>
<?php
#print '<pre>'; print_r($nodes); print '</pre>';
foreach($nodes as $node) {
    #print '<pre>'; print_r($config[$node]); print '</pre>';
	if (isset($astdb[$node]))
		$info = $astdb[$node][1] . ' ' . $astdb[$node][2] . ' ' . $astdb[$node][3];
	else
		$info = "Node not in database";
    if (($info == "Node not in database" ) || (isset($config[$node]['hideNodeURL']) && $config[$node]['hideNodeURL'] == 1)) {
        $nodeURL = $node;
        $title = "$node - $info";
    } else {
        $nodeURL = "http://stats.allstarlink.org/nodeinfo.cgi?node=$node";
        $bubbleChart = "http://stats.allstarlink.org/getstatus.cgi?$node";
    	$title = "Node <a href=\"$nodeURL\" class=\"link-light\" target=\"_blank\">$node</a> - $info ";
    	$title .= "<a href=\"$bubbleChart\" class=\"link-light\" target=\"_blank\" id=\"bubblechart\">Bubble Chart</a>";
    }
?>
<div class="card my-3"> <!-- Node card -->
  <h2 class="card-header bg-dark text-white"><?php echo $title; ?></h2>
  <div class="card-body">
    <div class="container overflow-hidden connect-container"> <!-- Connection management box -->
      <div class="row gy-3 py-3 bg-light">
        <label class="col-auto col-form-label col-form-label-sm" for="input-nodenum-<?php echo $node ?>">Connect <b><?php echo $node ?></b> to:</label>
        <div class="col-sm"><input type="text" class="form-control form-control-sm" placeholder="Node number" id="input-nodenum_<?php echo $node ?>"></div>
        <label class="col-auto col-form-label col-form-label-sm" for="select-conntype-<?php echo $node ?>">Connection type: </label>
        <div class="col-sm">
          <select class="form-select form-select-sm" id="select-conntype_<?php echo $node ?>">
            <option value="connect" selected>TX/RX</option>
            <option value="permanet">TX/RX - Permanent</option>
            <option value="monitor">RX Only (Monitor)</option>
            <option value="localmonitor">Local Monitor</option>
          </select>
        </div>
        <div class="col-sm"><button type="button" class="btn btn-primary btn-sm connect-button" id="connect-button_<?php echo $node ?>">Connect</button></div>
      </div>
    </div> <!-- Connection management box -->
    <h3 class="mt-3">Connected Nodes:</h3>
    <div class="table-responsive"> <!-- Connected nodes table -->
      <table class="table table-hover" id="table_<?php echo $node ?>">
        <thead>
          <tr>
            <th scope="col">Node</th>
            <th scope="col">Node Information</th>
            <th scope="col">Last PTT</th>
            <th scope="col">Link</th>
            <th scope="col">Direction</th>
            <th scope="col">Connected</th>
            <th scope="col">Mode</th>
            <th scope="col">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="8">Waiting...</td>
          </tr>
        </tbody>
      </table>
    </div> <!-- Connected nodes table -->
  </div>
</div> <!-- Node card -->
<?php
}
?>
</div>
<div id="blinky">
</div>
<?php include "footer.inc"; ?>
