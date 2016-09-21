<?php include 'Modules/antibodies/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="contatiner">
	<div class="col-md-10 col-md-offset-1">
	
		<div class="page-header">
			<h1>
				Protocoles<br> <small></small>
			</h1>
		</div>
	
		<table id="dataTable" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th><a href="protocols/index/anticorps">Anticorps</a></th>
					<th><a href="protocols/index/no_h2p2">No H2P2</a></th>
					<th><a href="protocols/index/kit">KIT</a></th>
					<th><a href="protocols/index/no_proto">No Proto</a></th>
					<th><a href="protocols/index/proto">Proto</a></th>
					<th><a href="protocols/index/fixative">Fixative</a></th>
					<th><a href="protocols/index/option">Option</a></th>
					<th><a href="protocols/index/enzyme">Enzyme</a></th>
					<th><a href="protocols/index/dem">Dém</a></th>
					<th><a href="protocols/index/acl_inc">AcI Inc</a></th>
					<th><a href="protocols/index/linker">Linker</a></th>
					<th><a href="protocols/index/inc">Linker Inc</a></th>
					<th><a href="protocols/index/acll">acII</a></th>
					<th><a href="protocols/index/inc2">acII Inc</a></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $protocols as $protocol ) : ?> 
				
				<tr>
					<?php $protocolId = $this->clean ( $protocol ['id'] ); ?>
					<?php if(  $protocol ['associe'] == 1){
						?>
							<td><?php echo  $protocol ['anticorps'] ?></td>
							<td><?php echo  $protocol ['no_h2p2'] ?></td>
						<?php
					}
					else{
						?>
						<td colspan="2" class="text-center">Général</td>
						<?php
					}
					?>
					
				    <td><?php echo  $this->clean ( $protocol ['kit'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['no_proto'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['proto'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['fixative'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['option_'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['enzyme'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['dem'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['acl_inc'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['linker'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['inc'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['acll'] ); ?></td>
				    <td><?php echo  $this->clean ( $protocol ['inc2'] ); ?></td>
				    
				    <td>
				      <button type='button' onclick="location.href='protocolsedit/<?php echo $id_space ?>/<?php echo  $protocolId ?>'" class="btn btn-xs btn-primary">Edit</button>
				    </td>  
	    		</tr>
	    		<?php endforeach; ?>
				
			</tbody>
		</table>

	</div>
</div>

<?php endblock();
