<template>
	<div class="home">
		<div class="container mt-8 mb-8 sm:mb-16 px-4">
			<div class="flex flex-wrap justify-between items-center">
				<img
					class="border-0"
					src="@images/logo-anymarket.png"
					alt="Anymarket"
				/>
				<div>
					<router-link to="export" class="btn btn-base mt-6 sm:mt-0">
						Exportar em massa
					</router-link>
				</div>
			</div>
		</div>
		<StatusBar />
		<div class="container px-4 mt-16">
			<div class="flex mb-8">
				<p class="title">Configurações</p>
			</div>
			<form class="flex flex-col">
				<div class="form-group">
					<label for="onserpLicense">Licensa</label>
					<div class="input">
						<input
							id="onserpLicense"
							name="onserpLicense"
							type="text"
						/>
						<div class="help-text">
							Se você não tem uma licensa
							<a>clique aqui para gerar uma</a>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="anymarketToken">Token da ANYMARKET</label>
					<div class="input">
						<input
							id="anymarketToken"
							name="anymarketToken"
							type="text"
						/>
					</div>
				</div>
				<div class="form-group">
					<label for="anymarketOI">OI</label>
					<div class="input">
						<input
							id="anymarketOI"
							name="anymarketOI"
							type="text"
						/>
					</div>
				</div>
				<div class="form-group">
					<label for="env">Ambiente</label>
					<div class="input relative">
						<toggle-button
							id="env"
							:value="false"
							:color="{
								checked: '#3366FF',
								unchecked: '#555770',
								disabled: '#CCCCCC',
							}"
						/>
						<span class="absolute left-0 ml-16">
							Usar ambiente de homologação
						</span>
						<div class="help-text">
							Marque a opção se o integrador informar que seu
							ambiente é de Testes (Sandbox)
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="callbackURL">URL para Callback</label>
					<div class="input input-copy">
						<input
							type="text"
							id="callbackURL"
							name="callbackURL"
							readonly
							v-model="callbackURL"
						/>
						<button
							class="btn clipboard"
							@click.prevent="copiedTooltip"
							data-clipboard-target="#callbackURL"
							v-tooltip="{
								content: 'Copiado!',
								trigger: 'manual',
								offset: '10',
								show: copiedTooltipIsOpen,
							}"
						>
							Copiar
						</button>
					</div>
				</div>
				<div class="form-group mt-16">
					<label for=""></label>
					<button type="submit" class="btn btn-base w-32">
						Salvar
					</button>
				</div>
			</form>
		</div>
	</div>
</template>

<script>
import StatusBar from '../components/StatusBar'
import { ToggleButton } from 'vue-js-toggle-button'

export default {
	name: 'Home',
	components: {
		StatusBar,
		ToggleButton,
	},
	data: () => {
		return {
			copiedTooltipIsOpen: false,
			callbackURL: 'https://sitedocliente.com/wp-json/anymarket',
		}
	},
	methods: {
		copiedTooltip(e) {
			this.copiedTooltipIsOpen = true
			e.target.setAttribute('disabled', 'disabled')

			setTimeout(() => {
				this.copiedTooltipIsOpen = false
				e.target.removeAttribute('disabled')
			}, 1000)
		},
	},
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped></style>
