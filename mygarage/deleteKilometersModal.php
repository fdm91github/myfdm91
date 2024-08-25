<!-- Modale per l'eliminazione di una voce delle percorrenze -->
<div class="modal fade" id="deleteKilometersModal" tabindex="-1" role="dialog" aria-labelledby="deleteKilometersModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteKilometersModalLabel">Elimina percorrenza</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteKilometersStatus"></div>
                <p>Sei sicuro di voler eliminare questa percorrenza?</p>
                <form id="deleteKilometersForm" method="POST" action="deleteKilometers.php">
                    <input type="hidden" name="id" id="deleteKilometersId">
                    <button type="submit" class="btn btn-danger btn-block">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deleteKilometersModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#deleteKilometersId').val(id);
		});

        $('#deleteKilometersForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteKilometers.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteKilometersStatus').html('<div class="alert alert-success">Percorrenza eliminata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteKilometersStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
