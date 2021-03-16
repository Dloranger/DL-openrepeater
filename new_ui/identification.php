<?php
// --------------------------------------------------------
// SESSION CHECK TO SEE IF USER IS LOGGED IN.
session_start();
if ((!isset($_SESSION['username'])) || (!isset($_SESSION['userID']))){
	header('location: index.php'); // If they aren't logged in, send them to login page.
} elseif (!isset($_SESSION['callsign'])) {
	header('location: wizard/index.php'); // If they are logged in, but they haven't set a callsign then send them to setup wizard.
} else { // If they are logged in and have set a callsign, show the page.
// --------------------------------------------------------



/*
$customJS = 'dropzone.js, upload-file.js'; // 'file1.js, file2.js, ... '
$customCSS = 'upload-file.css'; // 'file1.css, file2.css, ... '
*/

$customJS = 'page-identification.js, orp-audio-player.js, dropzone.js, upload-file.js, morse-resampler.js, morse-XAudioServer.js, morse.js, morse-main.js'; // 'file1.js, file2.js, ... '
$customCSS = 'page-identification.css, orp-audio-player.css, upload-file.css'; // 'file1.css, file2.css, ... '

include('includes/header.php');

$AudioFiles = new AudioFiles();
$identificationAudio = $AudioFiles->get_audio_files('identification');
?>


        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_full">
                <h3><i class="fa fa-volume-up"></i> <?=_('Identification')?></h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">



              <div class="col-md-6 col-xs-12">


                <div class="x_panel">
                  <div class="x_title"><h4><?=_('Short ID Settings')?></h4></div>

                  <div class="x_content">

                    <form class="form-horizontal form-label-left input_mask">

                      <div class="form-group">
                        <div class="btn-group btn-group-sm id-type" data-toggle="buttons">
                          <label class="btn btn-default<?= $settings['ID_Short_Mode'] == 'disabled' ? ' active':'' ?>"><input type="radio" name="ID_Short_Mode" id="ID_Short_Mode1"  value="disabled"<?= $settings['ID_Short_Mode'] == 'disabled' ? ' checked':'' ?>><?=_('Disabled')?></label>
                          <label class="btn btn-default<?= $settings['ID_Short_Mode'] == 'morse' ? ' active':'' ?>"><input type="radio" name="ID_Short_Mode" id="ID_Short_Mode2" value="morse"<?= $settings['ID_Short_Mode'] == 'morse' ? ' checked':'' ?>><?=_('Morse Only')?></label>
                          <label class="btn btn-default<?= $settings['ID_Short_Mode'] == 'voice' ? ' active':'' ?>"><input type="radio" name="ID_Short_Mode" id="ID_Short_Mode3" value="voice"<?= $settings['ID_Short_Mode'] == 'voice' ? ' checked':'' ?>><?=_('Basic Voice')?></label>
                          <label class="btn btn-default<?= $settings['ID_Short_Mode'] == 'custom' ? ' active':'' ?>"><input type="radio" name="ID_Short_Mode" id="ID_Short_Mode4" value="custom"<?= $settings['ID_Short_Mode'] == 'custom' ? ' checked':'' ?>><?=_('Custom')?></label>
                        </div>
                      </div>

                      <div id="ID_Short_Interval_Grp" class="form-group">
                        <label class="control-label col-md-6 col-sm-3 col-xs-5"><?=_('Short ID Time')?>
						  <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('...')?>"></i>
                        </label>
                        <div class="col-md-6 col-sm-9 col-xs-7">
                          <input type="number" id="ID_Short_IntervalMin" name="ID_Short_IntervalMin" class="form-control" value="<?= $settings['ID_Short_IntervalMin'] ?>" placeholder="<?=_('Minutes')?>" required>
                        </div>
                      </div>

                      <div id="ID_Short_Custom_Audio_Grp" class="form-group">
                        <label class="control-label col-md-6 col-sm-3 col-xs-5"><?=_('Audio File')?>
                        	<i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('...')?>"></i>
                        </label>
                        <div class="col-md-6 col-sm-9 col-xs-7">
						  <select id="ID_Short_CustomFile" name="ID_Short_CustomFile" class="form-control">
							<?php
								$activeShortFile = $settings['ID_Short_CustomFile'];
								foreach($identificationAudio as $curFile) { 
									$curOption = '<option value="'.$curFile['fileName'].'"';
									$curOption .= $activeShortFile == $curFile['fileName'] ? ' selected': '';
									$curOption .= '>'.$curFile['fileLabel'].'</option>';
									echo $curOption;
								}
							?>
						  </select>
                        </div>
                      </div>

                      <div id="ID_After_TX_Grp" class="form-group col-md-6 col-sm-6 col-xs-12">
                        <div class="gray-box">
                        <label class="control-label col-md-9"><?=_('ID after TX')?>
						  <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('...')?>"></i>
                        </label>
                        <div class="col-md-3">
                          <input id="ID_Only_When_Active" name="ID_Only_When_Active" type="checkbox" class="js-switch"<?= $settings['ID_Only_When_Active'] == 'True' ? ' checked':'' ?>/> 
                        </div>
                        </div>
                      </div>

                      <div id="ID_Short_Append_Morse_Grp" class="form-group col-md-6 col-sm-6 col-xs-12">
                        <div class="gray-box">
                        <label class="control-label col-md-9"><?=_('Append Morse ID')?>
						  <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('...')?>"></i>
                        </label>
                        <div class="col-md-3">
                          <input id="ID_Short_AppendMorse" name="ID_Short_AppendMorse" type="checkbox" class="js-switch"<?= $settings['ID_Short_AppendMorse'] == 'True' ? ' checked':'' ?>/> 
                        </div>
                        </div>
                      </div>

                    </form>
                  </div>
                </div>





                <div class="x_panel">
                  <div class="x_title"><h4><?=_('Long ID Settings')?></h4></div>

                  <div class="x_content">

                    <form class="form-horizontal form-label-left input_mask">

                      <div class="form-group">
                        <div class="btn-group btn-group-sm id-type" data-toggle="buttons">
                          <label class="btn btn-default<?= $settings['ID_Long_Mode'] == 'disabled' ? ' active':'' ?>"><input type="radio" name="ID_Long_Mode" id="ID_Long_Mode1"  value="disabled"<?= $settings['ID_Long_Mode'] == 'disabled' ? ' checked':'' ?>><?=_('Disabled')?></label>
                          <label class="btn btn-default<?= $settings['ID_Long_Mode'] == 'morse' ? ' active':'' ?>"><input type="radio" name="ID_Long_Mode" id="ID_Long_Mode2" value="morse"<?= $settings['ID_Long_Mode'] == 'morse' ? ' checked':'' ?>><?=_('Morse Only')?></label>
                          <label class="btn btn-default<?= $settings['ID_Long_Mode'] == 'voice' ? ' active':'' ?>"><input type="radio" name="ID_Long_Mode" id="ID_Long_Mode3" value="voice"<?= $settings['ID_Long_Mode'] == 'voice' ? ' checked':'' ?>><?=_('Basic Voice')?></label>
                          <label class="btn btn-default<?= $settings['ID_Long_Mode'] == 'custom' ? ' active':'' ?>"><input type="radio" name="ID_Long_Mode" id="ID_Long_Mode4" value="custom"<?= $settings['ID_Long_Mode'] == 'custom' ? ' checked':'' ?>><?=_('Custom')?></label>
                        </div>


                      </div>

                      <div id="ID_Long_Interval_Grp" class="form-group">
                        <label class="control-label col-md-6 col-sm-3 col-xs-5"><?=_('Long ID Time')?>
						  <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('The number of minutes between long identifications. The purpose of the long identification is to transmit some more information about the station. A good value for a repeater is every 60 minutes.')?>"></i>
                        </label>
                        <div class="col-md-6 col-sm-9 col-xs-7">
                          <input id="ID_Long_IntervalMin" name="ID_Long_IntervalMin" type="number" class="form-control" value="<?= $settings['ID_Long_IntervalMin'] ?>" placeholder="<?=_('Minutes')?>">
                        </div>
                      </div>

                      <div id="ID_Long_Custom_Audio_Grp" class="form-group">
                        <label class="control-label col-md-6 col-sm-3 col-xs-5"><?=_('Audio File')?>
                        	<i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('...')?>"></i>
                        </label>
                        <div class="col-md-6 col-sm-9 col-xs-7">
						  <select id="ID_Long_CustomFile" name="ID_Long_CustomFile" class="form-control">
							<?php
								$activeShortFile = $settings['ID_Long_CustomFile'];
								foreach($identificationAudio as $curFile) { 
									$curOption = '<option value="'.$curFile['fileName'].'"';
									$curOption .= $activeShortFile == $curFile['fileName'] ? ' selected': '';
									$curOption .= '>'.$curFile['fileLabel'].'</option>';
									echo $curOption;
								}
							?>
						  </select>
                        </div>
                      </div>



<!-- <div class="divider"></div> -->

                      <div id="ID_Long_Annc_Time_Grp" class="form-group col-md-6 col-sm-6 col-xs-12">
                        <div class="gray-box">
                        <label class="control-label col-md-9"><?=_('Announce Time')?>
						  <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('...')?>"></i>
                        </label>
                        <div class="col-md-3">
                          <input id="ID_Long_AppendTime" name="ID_Long_AppendTime" type="checkbox" class="js-switch"<?= $settings['ID_Long_AppendTime'] == 'True' ? ' checked':'' ?>/> 
                        </div>
                        </div>
                      </div>

                      <div id="ID_Long_Append_Morse_Grp" class="form-group col-md-6 col-sm-6 col-xs-12">
                        <div class="gray-box">
                        <label class="control-label col-md-9"><?=_('Append Morse ID')?>
						  <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('...')?>"></i>
                        </label>
                        <div class="col-md-3">
                          <input id="ID_Long_AppendMorse" name="ID_Long_AppendMorse" type="checkbox" class="js-switch"<?= $settings['ID_Long_AppendMorse'] == 'True' ? ' checked':'' ?>/> 
                        </div>
                        </div>
                      </div>


                    </form>
                  </div>
                </div>



                <?php $callSign = strtoupper($settings['callSign']); ?>
                <div class="x_panel">
                  <div class="x_title"><h4><?=_('Global Morse ID Settings')?></h4></div>

                  <div class="x_content">

                    <form class="form-horizontal form-label-left input_mask">

                      <div class="form-group">
                        <label class="control-label col-md-12"><?=_('Morse Callsign')?>
						  <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('Use the following callsign variation for ALL morse code identification.')?>"></i>
                        </label>

                        <div class="btn-group btn-group-sm col-md-12" data-toggle="buttons">
                          <label class="btn btn-default<?= $settings['ID_Morse_Suffix'] == '' ? ' active':'' ?>"><input type="radio" name="ID_Morse_Suffix" id="morseSuffix1" value=""<?= $settings['ID_Morse_Suffix'] == '' ? ' checked':'' ?>><?=$callSign?></label>
                          <label class="btn btn-default<?= $settings['ID_Morse_Suffix'] == '/R' ? ' active':'' ?>"><input type="radio" name="ID_Morse_Suffix" id="morseSuffix2" value="/R"<?= $settings['ID_Morse_Suffix'] == '/R' ? ' checked':'' ?>><?=$callSign?><strong>/R</strong></label>
                          <label class="btn btn-default<?= $settings['ID_Morse_Suffix'] == '/RPT' ? ' active':'' ?>"><input type="radio" name="ID_Morse_Suffix" id="morseSuffix3" value="/RPT"<?= $settings['ID_Morse_Suffix'] == '/RPT' ? ' checked':'' ?>><?=$callSign?><strong>/RPT</strong></label>
                        </div>
                      </div>


                      <div class="form-group">
                        <label class="control-label col-md-12"><?=_('Global speed and tone')?>
                        	<i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=_('Set the speed (WPM) and tone (Hz) of ALL morse code identification.')?>"></i>
                        </label>

                        <div class="col-md-12">
	                        <div class="col-md-12">
								<div class="knob_wrapper">
									<input class="knob" id="ID_Morse_WPM" name="ID_Morse_WPM" data-width="130" data-height="85" data-min="5" data-max="35" data-step="5" data-angleOffset=-90 data-angleArc=180 data-fgColor="#8dc63f" value="<?=$settings['ID_Morse_WPM']?>">
									<label><?=_('WPM')?></label>									
								</div>

								<div class="knob_wrapper">
									<input class="knob" id="ID_Morse_Pitch" name="ID_Morse_Pitch" data-width="130" data-height="85" data-min="400" data-max="1200" data-step="100" data-angleOffset=-90 data-angleArc=180 data-fgColor="#8dc63f" value="<?=$settings['ID_Morse_Pitch']?>">
									<label><?=_('Pitch (Hz)')?></label>
								</div>
	                        </div>

	                        <div class="col-md-12">
								<button id="morsePreview" type="button" class="btn btn-primary"><i class="fa fa-play"></i> <?=_('Preview')?></button>
								<input type="hidden" id="callSign" value="<?=$callSign?>">
								<input type="hidden" id="morseOutput" value="">
	                        </div>
                        </div>

                      </div>

                    </form>
                  </div>

                </div>

              </div>






              <div class="col-md-6  col-xs-12">



                <div class="x_panel">
                  <div class="x_title">
                    <h4 class="navbar-left"><?=_('Identification Clip Library')?></h4>
                    <div class="nav navbar-right">
                      <button type="button" class="btn btn-success upload_file" data-upload-type="identification"><i class="fa fa-upload"></i> <?=_('Upload ID Clip')?></button>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					
                    <table id="id_library" class="audio-table table table-striped dt-responsive nowrap" cellspacing="0" width="100%">
					  <thead>
						  <tr>
							  <th><?=_('Name')?></th>
							  <th class="button_grp" style="text-align: right;"><?=_('Actions')?></th>
						  </tr>
					  </thead>   

                      <tbody>
						<?php
							foreach($identificationAudio as $curKey => $curFile) { 
						?>

							<tr id="clip<?=$curKey?>" data-row-name="<?=$curFile['fileLabel']?>" data-row-file="<?=$curFile['fileName']?>">
								<td>
									<div id="player<?=$curKey?>" class="orp_player">
									    <audio preload="true">
									        <source src="<?=$curFile['fileURL']?>">
									    </audio>
									    <button class="play"><span></span></button>
									</div>

									<span class="audio_name"><?=$curFile['fileLabel']?></span>
								</td>

								<td>			
									<div class="btn-group btn-group-sm audio-actions" role="group">
										<button class="rename_file btn btn-secondary" type="button"><i class="fa fa-repeat"></i> <?=_('Rename')?></button>
										<button class="delete_file btn btn-danger" type="button"><i class="fa fa-remove"></i> <?=_('Delete')?></button>
									</div>
								</td>
							</tr>

						<?php } ?>

                      </tbody>



                    </table>
					
					



                  </div>
                </div>

              </div>
           </div>
          </div>
        </div>
        <!-- /page content -->

<script>
	var modal_RenameTitle = '<?=_('Rename Clip')?>';
	var modal_RenameBody = '<p><?=_('Please enter the new file name')?></p>';
	var modal_RenameBtnOK = '<?=_('Rename')?>';

	var modal_DeleteTitle = '<?=_('Delete Clip')?>';
	var modal_DeleteBody = '<p><?=_('Are you sure that you wish to delete the following clip?')?></p>';
	var modal_DeleteBtnOK = '<?=_('Delete')?>';
	var modal_DeleteBtnOKclass = 'btn-danger'

	var modal_DeleteErrorTitle = '<?=_('Delete Error')?>';
	var modal_DeleteErrorBody = '<p><?=_('You cannot delete the following clip as it is in use.')?></p>';
	var modal_DeleteErrorBtnOK = '<?=_('Dismiss')?>';
</script>


<!-- Upload Dialog Modal -->
<script>
	var modal_UploadTitle = '<?=_('Upload Identification')?>';
	var modal_dzDefaultText = '<?=_('Drag files here or click to browse for files.')?>';
	var modal_dzCustomDesc = '<?=_('Upload your own custom recorded audio files for identification. The file should be in MP3 or WAV format and any excess dead air should be trimmed off of the clip and audio levels normalized. DO NOT UPLOAD FILES THAT CONTAIN MUSIC, CLIPS OF MUSIC, OR OTHER COPYRIGHTED MATERIAL...IT IS ILLEGAL.')?>';
	var uploadSuccessTitle = '<?=_('Upload Complete')?>';
	var uploadSuccessText = '<?=_('New custom identification was successfully uploaded to library.')?>';
</script>

<?php include('includes/footer.php'); ?>

<?php
// --------------------------------------------------------
// SESSION CHECK TO SEE IF USER IS LOGGED IN.
 } // close ELSE to end login check from top of page
// --------------------------------------------------------
?>