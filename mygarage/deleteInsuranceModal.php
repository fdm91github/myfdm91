<!-- Modale per l'eliminazione di una voce delle assicurazioni -->
<div class="modal fade" id="deleteInsuranceModal" tabindex="-1" role="dialog" aria-labelledby="deleteInsuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteInsuranceModalLabel">Elimina veicolo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteInsuranceStatus"></div>
                <p>Sei sicuro di voler eliminare questa assicurazione?</p>
                <form id="deleteInsuranceForm" method="POST" action="deleteInsurance.php">
                    <input type="hidden" name="id" id="deleteInsuranceId">
                    <button type="submit" class="btn btn-danger btn-block">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deleteInsuranceModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#deleteInsuranceId').val(id);
		});

        $('#deleteInsuranceForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteInsurance.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteInsuranceStatus').html('<div class="alert alert-success">Assicurazione eliminata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteInsuranceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
