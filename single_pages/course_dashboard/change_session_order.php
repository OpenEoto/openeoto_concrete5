<?php defined('C5_EXECUTE') or die(_("Access Denied."));
$vt = Loader::helper('validation/token');?>
<a href="<?php echo $this->action('view') . '?courseID=' . $courseID; ?>">&laquo; <?php echo t('Course dashboard'); ?></a>

<h2 class="page-header"><?php echo $courseTitle; ?></h2>

<form method="POST">
<?php print $vt->output(); ?>
    <ul id="open-courses-sessions-sortable">
        <?php foreach ($sessions as $session): ?>
            <li data-cID="<?php echo $session->getCollectionID(); ?>"><a class="btn"><i class="fa fa-arrows-v"></i> <?php echo $session->getCollectionName(); ?></a></li>
        <?php endforeach; ?>
    </ul>

    <button type="submit" class="btn btn-primary disabled" id="open-courses-sessions-sortable-submit-btn"><?php echo t('Save'); ?></button>
    <input name="cIDs" id="open-courses-sessions-sortable-hidden-field" type="hidden" />
</form>

<script type="text/javascript">
    $(function() {
        
        var changed = false;
        
        $("#open-courses-sessions-sortable").sortable({
            stop: function(event, ui) {

                var cIDs = $(this).sortable("toArray",{'attribute':'data-cID'});
                $("#open-courses-sessions-sortable-hidden-field").val(JSON.stringify(cIDs));            
                $("#open-courses-sessions-sortable-submit-btn").removeClass('disabled');
                changed = true;
            }
        });

    });
</script>