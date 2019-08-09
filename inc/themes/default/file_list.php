<div class="directory" data-path="<?php echo html($path);?>">
	<?php if ($num_files > 0) : ?>
		<?php for ($i = 0; $i < $num_files; $i++) : ?>
			<div class="directory-file">
				<?php if ($files[$i]['is_dir']) : ?>
					<a href="?dir=<?php echo rawurlencode($files[$i]['path']);?>">
				<?php endif;?>
				<span class="directory-file-icon"><?php echo $files[$i]['fa_icon'];?></span>
				<span class="directory-file-name"><?php echo html($files[$i]['name']);?></span>
				<?php if ($files[$i]['is_dir']) : ?>
					</a>
				<?php endif;?>
			</div>
		<?php endfor;?>
	<?php else : ?>
		<p><?php echo __('Empty directory');?></p>
	<?php endif;?>
	<!--<table>
		<thead>
			<tr>
				<th><?php echo __('Filename');?></th>
				<th><?php echo __('Filesize');?></th>
				<th><?php echo __('Filetype');?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($num_files > 0) : ?>
				<?php for ($i = 0; $i < $num_files; $i++) : ?>
					<tr>
						<td>
							<?php if ($files[$i]['is_dir']) : ?>
								<a href="?dir=<?php echo rawurlencode($files[$i]['path']);?>"><?php echo html($files[$i]['name']);?></a>
							<?php else : ?>
								<?php echo html($files[$i]['name']);?>
							<?php endif;?>
						</td>
						<td><?php echo html($files[$i]['print_size']);?></td>
						<td></td>
					</tr>
				<?php endfor;?>
			<?php else : ?>
				<tr><td colspan="3"><?php echo __('Empty directory');?></td></tr>
			<?php endif;?>
		</tbody>
	</table>-->
</div>