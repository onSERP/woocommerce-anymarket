<template>
	<div class="w-full sm:w-1/2 md:w-56 mt-4 xl:mt-0 px-2">
		<div class="flex flex-col bg-white py-4 shadow-lg rounded">
			<div class="text-base text-center mb-4">
				{{ title }}
			</div>
			<div
				class="text-xl font-bold text-center"
				:class="{
					'text-green-500': alertColor === 'green',
					'text-red-400': alertColor === 'red',
				}"
			>
				{{ dataText }}
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'StatusBox',
	props: {
		title: String,
		dataText: String,
		alertColor: String,
	},
}
</script>

<style></style>
