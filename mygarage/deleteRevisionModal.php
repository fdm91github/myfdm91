<!-- Modale per l'eliminazione di una voce delle revisioni -->
<div class="modal fade" id="deleteRevisionModal" tabindex="-1" role="dialog" aria-labelledby="deleteRevisionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRevisionModalLabel">Elimina revisione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteRevisionStatus"></div>
                <p>Sei sicuro di voler eliminare questa revisione?</p>
                <form id="deleteRevisionForm" method="POST" action="deleteRevision.php">
                    <input type="hidden" name="id" id="deleteRevisionId">
                    <button type="submit" class="btn btn-danger btn-block">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deleteRevisionModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#deleteRevisionId').val(id);
		});

        $('#deleteRevisionForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteRevision.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteRevisionStatus').html('<div class="alert alert-success">Revisione eliminata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteRevisionStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
