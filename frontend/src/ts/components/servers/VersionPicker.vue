<template>
    <select @input="$emit('input', $event.target.value)" :value="internalValue">
        <option v-for="version of versionList" v-bind:key="version.versionName" :value="version">{{ version.versionName }}</option>
    </select>
</template>

<script lang="ts">
    import Vue from 'vue'
    import Component from 'vue-class-component';
    import axios from 'axios';
    import { Prop, Watch } from 'vue-property-decorator';

    class Version {
        constructor(
            public versionName: string,
            public isSnapshot: boolean
        ) {

        }

        public toString() {
            return this.versionName;
        }
    }

    const MANIFEST_URL = "https://launchermeta.mojang.com/mc/game/version_manifest.json";

    @Component
    export default class VersionPicker extends Vue {
        private versions: Array<Version> = [];

        @Prop({required: false, default: true})
        public showSnapshots!: boolean;

        @Prop({default: ''})
        public value!: string;
        
        private internalValue: Version | string = '';

        public get versionList() {
            return this.versions.filter(v => this.showSnapshots || !v.isSnapshot);
        }

        @Watch('value')
        private updateValue(newValue: string, oldValue: string) {
            if (newValue === oldValue) return;

            const version = this.versions.find(v => v.versionName === newValue)
            if (version !== undefined) {
                this.internalValue = version;
            }
        }

        async created() {
            try {
                const versionManifest = await axios.get(MANIFEST_URL);
                let versions: Array<Version> = [];
                if (versionManifest.status === 200) {
                    for (let version of versionManifest.data.versions) {
                        versions.push(new Version(
                            version.id,
                            version.type === 'snapshot'
                        ));
                    }
                }
                this.versions = versions;
                setTimeout(() => this.internalValue = this.value, 0);
            } catch {
                return;
            }
        }
    }
</script>

<style lang="scss" scoped>

</style>