<!-- Modale per l'il ripristino di un veicolo -->
<div class="modal fade" id="restoreVehicleModal" tabindex="-1" aria-labelledby="restoreVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreVehicleModalLabel">Ripristina veicolo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="restoreVehicleStatus"></div>
                <p>Sei sicuro di voler ripristinare questo veicolo?</p>
                <form id="restoreVehicleForm" method="POST" action="restoreVehicle.php">
                    <input type="hidden" name="id" id="restoreVehicleId">
                    <button type="submit" class="btn btn-primary w-100">Ripristina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#restoreVehicleModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#restoreVehicleId').val(id);
		});

        $('#restoreVehicleForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'restoreVehicle.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#restoreVehicleStatus').html('<div class="alert alert-success">Veicolo ripristinato con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#restoreVehicleStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
