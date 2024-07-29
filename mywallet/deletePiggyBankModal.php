<!-- Modale per l'eliminazione di una voce al salvadanaio -->
<div class="modal fade" id="deletePiggyBankModal" tabindex="-1" role="dialog" aria-labelledby="deletePiggyBankModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePiggyBankModalLabel">Elimina dal salvadanaio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deletePiggyBankStatus"></div>
                <p>Sei sicuro di voler eliminare questa voce?</p>
                <form id="deletePiggyBankForm">
                    <input type="hidden" name="id" id="deletePiggyBankId">
                    <button type="submit" class="btn btn-danger btn-block">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deletePiggyBankModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');
		
			$('#deletePiggyBankId').val(id);
		});
		
		$('#deletePiggyBankForm').submit(function(e) {
			e.preventDefault();
			$.ajax({
				url: 'deletePiggyBank.php',
				type: 'POST',
				data: $(this).serialize(),
				success: function(response) {
					if (response.status === 'success') {
						$('#deletePiggyBankStatus').html('<div class="alert alert-success">' + response.message + '</div>');
						setTimeout(function() { window.location.reload(); }, 1000);
					} else {
						$('#deletePiggyBankStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
					}
				},
				error: function() {
					$('#deletePiggyBankStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
				}
			});
		});
	});
</script>