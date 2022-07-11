<template>
    <Card>
        <h2 slot="title" style="margin-bottom: 20px;">地図表示</h2>
        <div class="google-map" ref="googleMap" style="height: 500px;"></div>
        <template v-if="Boolean(this.google) && Boolean(this.map)">
            <slot
                    :google="google"
                    :map="map"
            />
        </template>
        <br/>
        <Row :gutter="16">
            <Col span="12" style="text-align: right;">
                <Button type="success" size="large" @click="setPosition">位置確定</Button>
            </Col>
            <Col span="12">
                <Button type="info" size="large"  @click="toCancel">キャンセル</Button>
            </Col>
        </Row>
    </Card>
</template>

<script>
    export default {
        name: "GoogleMap",
        props: {
            apiKey: String,
        },

        data() {
            return {
                google: null,
                map: null,
                position: {},
                mapConfig: {
                    zoom: 12,
                    center: {lat:35.6803997,lng:139.7690174},
                    address: "東京"
                },
                infoWindow: null
            }
        },
        mounted() {
            this.google = window.google
            this.map = new this.google.maps.Map(this.$refs.googleMap, this.mapConfig);
        },
        methods: {
            initializeMap(mapConfig) {
                this.mapConfig = mapConfig
                if(mapConfig["center"]["lat"]){
                    this.map = new this.google.maps.Map(this.$refs.googleMap, mapConfig);
                    this.viewMap()
                }
                else{
                    try{
                        const geocoder = new this.google.maps.Geocoder();
                        geocoder.geocode({ address: '日本 ' + this.mapConfig.address }, (results, status) => {
                            if (status !== 'OK' || !results[0]) {
                                return
                            }
                            this.map.setCenter(results[0].geometry.location);
                            this.map.fitBounds(results[0].geometry.viewport);
                            this.mapConfig.center = {lat : results[0].geometry.location.lat(), lng: results[0].geometry.location.lng()}
                            this.viewMap()
                        });
                    }
                    catch (e) {
                    }
                }
            },
            getLatLngText(address, latLng) {
                return address + "緯度:" + latLng.lat + ", 経度" + latLng.lng
            },
            viewMap() {
                // Create the initial InfoWindow.
                let address = ''
                if (this.mapConfig.address) {
                    address = "所在地: " + this.mapConfig.address + "<br/>"
                }
                if(this.infoWindow) {
                    this.infoWindow.close();
                }
                this.infoWindow = new this.google.maps.InfoWindow({
                    content: this.getLatLngText(address, this.mapConfig.center),
                    position: this.mapConfig.center,
                });
                this.position = this.mapConfig.center
                // Configure the click listener.
                this.map.addListener("click", (mapsMouseEvent) => {
                    // Close the current InfoWindow.
                    this.infoWindow.close();
                    // Create a new InfoWindow.
                    this.infoWindow = new this.google.maps.InfoWindow({
                        position: mapsMouseEvent.latLng,
                    });
                    this.infoWindow.setContent(
                        this.getLatLngText(address, mapsMouseEvent.latLng.toJSON())
                    );
                    this.infoWindow.open(this.map);
                    this.position = {lat : mapsMouseEvent.latLng.lat(), lng: mapsMouseEvent.latLng.lng()};
                });
                this.infoWindow.open(this.map);
            },
            setPosition() {
                this.$emit('setPosition', this.position)
            },
            toCancel(){
                this.$emit('closeMap')
            }
        }
    }
</script>

<style scoped>

</style>
