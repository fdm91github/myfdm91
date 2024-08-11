<!-- Modale per l'eliminazione di una spesa extra -->
<div class="modal fade" id="deleteVehicleModal" tabindex="-1" role="dialog" aria-labelledby="deleteVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteVehicleModalLabel">Elimina veicolo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteVehicleStatus"></div>
                <p>Sei sicuro di voler eliminare questo veicolo?</p>
                <form id="deleteVehicleForm" method="POST" action="deleteVehicle.php">
                    <input type="hidden" name="id" id="deleteVehicleId">
                    <button type="submit" class="btn btn-danger btn-block">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deleteVehicleModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#deleteVehicleId').val(id);
		});

        $('#deleteVehicleForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteVehicle.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteVehicleStatus').html('<div class="alert alert-success">Veicolo eliminato con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteVehicleStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
