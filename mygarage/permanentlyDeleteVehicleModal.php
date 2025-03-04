<!-- Modale per l'eliminazione di una spesa extra -->
<div class="modal fade" id="permanentlyDeleteVehicleModal" tabindex="-1" aria-labelledby="permanentlyDeleteVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permanentlyDeleteVehicleModalLabel">Elimina veicolo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="permanentlyDeleteVehicleStatus"></div>
                <p>Sei sicuro di voler eliminare DEFINITIVAMENTE questo veicolo?</p>
                <form id="permanentlyDeleteVehicleForm" method="POST" action="permanentlyDeleteVehicle.php">
                    <input type="hidden" name="id" id="permanentlyDeleteVehicleId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#permanentlyDeleteVehicleModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#permanentlyDeleteVehicleId').val(id);
		});

        $('#permanentlyDeleteVehicleForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'permanentlyDeleteVehicle.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#permanentlyDeleteVehicleStatus').html('<div class="alert alert-success">Veicolo eliminato con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#permanentlyDeleteVehicleStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
