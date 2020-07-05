<template>
	<div class="container px-4">
		<div class="flex flex-wrap">
			<StatusBox
				title="Status da integração"
				data-text="ATIVA"
				alert-color="green"
			/>
			<StatusBox title="Produtos exportados" data-text="172/882" />
			<StatusBox title="Categorias exportadas" data-text="32/44" />
			<StatusBox title="Marcas exportadas" data-text="20/42" />
		</div>
	</div>
</template>

<script>
import StatusBox from './shared/StatusBox'

export default {
	components: {
		StatusBox,
	},
}
</script>

<style></style>
