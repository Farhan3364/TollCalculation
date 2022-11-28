<template>
    <div class="container">
        <div class="d-flex mt-5 justify-content-center">
            <button 
                type="button" 
                :class="['btn btn-outline-primary', {'btn-active': (selected_tab === 'entry')}]" 
                @click="selectTab('entry')"
                >
                Entry
            </button>
            <button 
                type="button" 
                 :class="['btn btn-outline-primary ms-5', {'btn-active': (selected_tab === 'exit')}]" 
                @click="selectTab('exit')"
                >
                Exit
            </button>
        </div>
        <form @submit.prevent="submit">
            <div class="mt-5" v-if="selected_tab === 'entry'">
                <div class="card">
                    <div class="card-header">Entry</div>
                    <div class="card-body">
                        <div class="col-6">
                            <select class="form-select" placeholder="Interchange" v-model="entry.interchange">
                                <option v-for="(obj, index) in interchanges" :key="index" :value="obj">{{ obj.name }}</option>
                            </select>
                        </div>
                        <div class="col-6 mt-5">
                            <input 
                                :class="['form-control', { 'is-invalid-noicon': (!$v.entry.number_plate.required) },
                                    {'is-invalid-noicon': (!$v.entry.number_plate.minLength)}]" 
                                v-mask="'AAA-###'" 
                                :masked="true" 
                                v-model="entry.number_plate" 
                                placeholder="LLL-NNN (L is a letter and N is a Number)" 
                            />
                            <span class="invalid-feedback" v-if="!$v.entry.number_plate.required">Number plate is required</span>
                            <span class="invalid-feedback" v-if="!$v.entry.number_plate.minLength">Please entrer valid number plate</span>
                        </div>
                        <div class="col-6 mt-5">
                            <date-picker
                                v-model="entry.date_time"
                                type="datetime"
                                value-type="YYYY-MM-DD hh:mm:ss"
                                format="DD-MM-YYYY hh:mm:ss"
                                placeholder="Date Time"
                                class="w-100"
                            >
                            </date-picker>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5" v-if="selected_tab === 'exit'">
                <div class="card">
                    <div class="card-header">Exit</div>
                    <div class="card-body">
                        <div class="row">
                            <!-- fields -->
                            <div class="col-6">
                                <select class="form-select" placeholder="Interchange" v-model="exit.interchange">
                                    <option v-for="(obj, index) in filterExitInterchanges" :key="index" :value="obj">{{ obj.name }}</option>
                                </select>
                                <div class="mt-5">
                                    <input 
                                         :class="['form-control', { 'is-invalid-noicon': (!$v.exit.number_plate.required) },
                                            {'is-invalid-noicon': (!$v.exit.number_plate.minLength)}]" 
                                        v-mask="'AAA-###'" 
                                        :masked="true" 
                                        v-model="exit.number_plate" 
                                        placeholder="LLL-NNN (L is a letter and N is a Number)" 
                                    />
                                    <span class="invalid-feedback" v-if="!$v.exit.number_plate.required">Number plate is required</span>
                                    <span class="invalid-feedback" v-if="!$v.exit.number_plate.minLength">Please entrer valid number plate</span>
                                </div>
                                <div class="mt-5">
                                    <date-picker
                                        v-model="exit.date_time"
                                        type="datetime"
                                        value-type="YYYY-MM-DD hh:mm:ss"
                                        format="DD-MM-YYYY hh:mm:ss"
                                        placeholder="Date Time"
                                        class="w-100"
                                    >
                                    </date-picker>
                                </div>
                            </div>
                            <!-- summary -->
                            <div class="col-6 px-5">
                                <h3 class="text-center">Break Down of Cost</h3>
                                <!-- Base Rate -->
                                <div class="d-flex justify-content-between">
                                    <h4>Base Rate</h4>
                                    <h4>{{ this.summary.baseRate }}</h4>
                                </div>
                                <!-- Distance Cost -->
                                <div class="d-flex justify-content-between">
                                    <h4>Distance Cost Breakdown</h4>
                                    <h4>{{ this.summary.distanceCost | onlyTwoDigitAfterDecimal}}</h4>
                                </div>
                                <!-- Sub Total -->
                                <div class="d-flex justify-content-between">
                                    <h4>Sub-Total</h4>
                                    <h4>{{ this.summary.subTotal | onlyTwoDigitAfterDecimal}}</h4>
                                </div>
                                <!-- Discount/other -->
                                <div class="d-flex justify-content-between">
                                    <h4>Disocunt/Other</h4>
                                    <h4>{{ (this.summary.discount ? this.summary.discount : 0) | onlyTwoDigitAfterDecimal}}</h4>
                                </div>
                                <!-- Total to be charges -->
                                <div class="d-flex justify-content-between">
                                    <h4 class="text-uppercase">Total To Be Charged</h4>
                                    <h4>{{ this.summary.total | onlyTwoDigitAfterDecimal}}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 mt-3">
                <button
                    type="submit" 
                    class="btn btn-success float-end"
                    :disabled="$v.$invalid || data_loading || crud_loading "
                    >
                    <span v-if="selected_tab === 'entry' && !crud_loading">Submit</span>
                    <span v-if="selected_tab === 'exit' && !crud_loading ">Calculate</span>
                    <span v-if="crud_loading"><i class="spinner-border spinner-border-sm"></i></span>
                </button>
            </div>
        </form>
        <div class="overlay" v-if="data_loading">
            <div class="loader spinner-border text-info" role="status"></div>
        </div>
    </div>
</template>

<script>
import { mask } from 'vue-the-mask'
import moment from 'moment';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import { required, minLength } from 'vuelidate/lib/validators';

export default {
    name: 'toll-calculation-component',
    directives: { mask },
    components: { DatePicker },
    data() {
        return {
            entry: {
                interchange: null,
                number_plate: null,
                date_time: moment().format('YYYY-MM-DD hh:mm:ss'),
            },
            exit: {
                interchange: null,
                number_plate: null,
                date_time: null,
            },
            selected_tab: 'entry',
            interchanges: [],
            data_loading: false,
            crud_loading: false,
            summary: {}
        }
    },
    validations() {
        if(this.selected_tab === 'entry'){
            return {
                entry: { 
                    interchange: {required}, 
                    number_plate: {required, minLength: minLength(7)}, 
                    date_time: {required}, 
                }
            }
        }
        else {
            return {
                exit: { 
                    interchange: {required}, 
                    number_plate: {required, minLength: minLength(7)}, 
                    date_time: {required}, 
                }
            }
        }
    },
    methods: {
        getFormData() {
            this.data_loading = true;
            axios({
                url: `/get/form/data`,
                method: 'GET',
            })
            .then(response => {
                this.data_loading = false;
                this.interchanges = response.data;
                this.entry.interchange = this.interchanges.find(obj => obj.km === 0);
            })
            .catch(error => {
                this.sweetAlertErrorToast(error.message);
            })
        },
        selectTab(type) {
            if (type === 'entry') {
                this.selected_tab = 'entry'
            }
            if (type === 'exit') {
                this.selected_tab = 'exit'
                this.exit.interchange = this.filterExitInterchanges[0];
                this.exit.number_plate = this.entry.number_plate;
                this.exit.date_time = moment().format('YYYY-MM-DD hh:mm:ss')
            }
        },
        submit(type) {
            if (!this.$v.$error) {
                this.crud_loading = true;
                axios({
                    url: `/save/vehicle/transaction`,
                    method: 'POST',
                    data: {type: this.selected_tab, data: this.selected_tab === 'entry' ? this.entry : this.exit} 
                })
                .then(response => {
                    this.crud_loading = false;
                    if (this.selected_tab === 'entry') {
                        this.sweetAlertSucessToast(response.data.message);
                    }
                    if (this.selected_tab === 'exit') {
                        this.sweetAlertSucessToast(response.data.message);
                        this.summary = response.data.summary
                    }
                })
                .catch(error => {
                    this.crud_loading = false;
                    if(error.response.status === 500){
                        this.sweetAlertErrorToast(error.response.statusText);
                    }
                    else {
                        this.sweetAlertErrorToast(error.response.data.error);
                    }
                })
            }
        }
    },
    computed: {
        filterExitInterchanges() {
            return this.interchanges.filter(obj => obj.id !== this.entry.interchange.id)
        }
    },
    filters: {
        onlyTwoDigitAfterDecimal(number) {
            if (number) {
                return parseFloat(number).toFixed(2);
            }
        },
    },
    mounted(){
        this.getFormData();
    },
}
</script>
<style scoped>
.overlay {
    opacity: .3;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: #000;
}
.overlay .loader {
    position: absolute;
    top: 50%;
    left: 50%;
}
.btn{
    width: 5rem;
}
.btn-active {
    color: var(--bs-btn-hover-color);
    background-color: var(--bs-btn-hover-bg);
    border-color: var(--bs-btn-hover-border-color);
}
.form-control.is-invalid-noicon {
    border-color: #DC3545;
    padding-right: 2.25rem !important;
}
.form-control.is-invalid-noicon:focus {
    box-shadow: 0 0 0 0.25rem rgb(220 53 69 / 50%);
}
.invalid-feedback {
    position: absolute;
    display: block;
    width: 100%;
    margin-top: unset;
    font-size: 0.785em;
    color: #dc3545;
}
</style>