<template>
    <div class="container my-4">
      <h1 class="mb-4 text-center">Správa Áut</h1>
  
      <!-- Filterovacie tlačidlá pre autá -->
      <div class="text-center mb-4">
        <!-- Tlačidlo pre filtrovanie registrovaných áut -->
        <button
          class="btn me-2"
          :class="currentFilter === 'is_registered' ? 'btn-primary' : 'btn-secondary'"
          @click="applyFilter('is_registered')"
        >
          Registrované
        </button>
        <!-- Tlačidlo pre abecedné zoradenie áut -->
        <button
          class="btn"
          :class="currentFilter === 'alfabetically' ? 'btn-primary' : 'btn-secondary'"
          @click="applyFilter('alfabetically')"
        >
          Abecedne
        </button>
        <!-- Tlačidlo pre zrušenie filtra áut -->
        <button
          class="btn ms-2"
          :class="currentFilter === '' ? 'btn-primary' : 'btn-secondary'"
          @click="applyFilter('')"
        >
          Všetky
        </button>
      </div>
  
      <!-- Tlačidlo pre zobrazenie/skrytie formulára pre auto -->
      <div class="text-center mb-4">
        <button class="btn btn-primary" @click="toggleForm">
          {{ showForm ? 'Skryť formulár' : 'Pridať nové auto' }}
        </button>
      </div>
  
      <!-- Formulár pre pridanie alebo úpravu auta -->
      <div v-if="showForm" class="card mb-4">
        <div class="card-body">
          <h4 class="card-title mb-3">
            {{ editingCar ? 'Upraviť auto' : 'Pridať nové auto' }}
          </h4>
          <form @submit.prevent="submitForm">
            <!-- Názov auta -->
            <div class="mb-3">
              <label for="name" class="form-label">Názov auta</label>
              <input
                type="text"
                id="name"
                class="form-control"
                v-model="form.name"
                required
              />
            </div>
  
            <!-- Checkbox: Registrované -->
            <div class="mb-3 form-check">
              <input
                type="checkbox"
                id="is_registered"
                class="form-check-input"
                v-model="form.is_registered"
              />
              <label for="is_registered" class="form-check-label">Registrované</label>
            </div>
  
            <!-- Registračné číslo (zobrazuje sa iba ak je auto registrované) -->
            <div class="mb-3" v-if="form.is_registered">
              <label for="registration_number" class="form-label">Registračné číslo</label>
              <input
                type="text"
                id="registration_number"
                class="form-control"
                v-model="form.registration_number"
                :required="form.is_registered"
              />
            </div>
  
            <!-- Tlačidlá pre uloženie alebo zrušenie -->
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-success me-2">
                {{ editingCar ? 'Aktualizovať auto' : 'Pridať auto' }}
              </button>
              <button type="button" class="btn btn-secondary" @click="resetForm">
                Zrušiť
              </button>
            </div>
          </form>
        </div>
      </div>
  
      <!-- Tabuľka áut -->
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Názov</th>
            <th>Registrované</th>
            <th>Registračné číslo</th>
            <th class="text-center">Akcie</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="car in cars" :key="car.id">
            <tr>
              <td>{{ car.name }}</td>
              <td>
                <span class="badge" :class="car.is_registered ? 'bg-success' : 'bg-secondary'">
                  {{ car.is_registered ? 'Áno' : 'Nie' }}
                </span>
              </td>
              <td>{{ car.registration_number }}</td>
              <td class="text-center">
                <button class="btn btn-sm btn-info me-2" @click="showParts(car)">
                  Diely
                </button>
                <button class="btn btn-sm btn-warning me-2" @click="editCar(car)">
                  Upraviť
                </button>
                <button class="btn btn-sm btn-danger" @click="deleteCar(car)">
                  Vymazať
                </button>
              </td>
            </tr>
            <!-- Rozbaľovací riadok pre diely, ak je dané auto rozbalené -->
            <tr v-if="expandedCarId === car.id">
              <td colspan="4">
                <div class="p-3 border rounded bg-light">
                  <h5 class="mb-3">Diely pre auto: {{ car.name }}</h5>
  
                  <!-- Filterovacie tlačidlá pre diely -->
                  <div class="d-flex justify-content-between mb-3">
                    <!-- Tlačidlo pre abecedné zoradenie dielov -->
                    <button
                      class="btn btn-secondary"
                      @click="togglePartsFilter"
                    >
                      {{ partsFilter === 'alphabetically' ? 'Abecedne (zrušiť)' : 'Abecedne' }}
                    </button>
                    <!-- Tlačidlo pre zobrazenie formulára na pridanie dielu -->
                    <button class="btn btn-secondary" @click="toggleAddPartForm">
                      Pridať diel
                    </button>
                  </div>
  
                  <!-- Zoznam dielov s update a delete tlačidlami -->
                  <ul class="list-group mb-3" v-if="selectedCarParts.length">
                    <li
                      v-for="part in selectedCarParts"
                      :key="part.id"
                      class="list-group-item"
                    >
                      <div v-if="editingPartId !== part.id" class="d-flex justify-content-between align-items-center">
                        <div>
                          <strong>{{ part.name }}</strong>
                          <div class="small text-muted">
                            Sériové číslo: {{ part.serialnumber }}
                          </div>
                        </div>
                        <div>
                          <button class="btn btn-sm btn-warning me-2" @click="editPart(part)">Upraviť</button>
                          <button class="btn btn-sm btn-danger" @click="deletePart(part)">Vymazať</button>
                        </div>
                      </div>
                      <!-- Inline formulár na aktualizáciu dielu -->
                      <div v-else>
                        <form @submit.prevent="updatePart(part)">
                          <div class="mb-2">
                            <input
                              type="text"
                              class="form-control"
                              v-model="partForm.name"
                              placeholder="Názov dielu"
                              required
                            />
                          </div>
                          <div class="mb-2">
                            <input
                              type="text"
                              class="form-control"
                              v-model="partForm.serialnumber"
                              placeholder="Sériové číslo"
                              required
                            />
                          </div>
                          <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-success me-2">Uložiť</button>
                            <button type="button" class="btn btn-sm btn-secondary" @click="cancelEditPart">Zrušiť</button>
                          </div>
                        </form>
                      </div>
                    </li>
                  </ul>
                  <p v-else class="text-muted">Žiadne diely zatiaľ pridané.</p>
  
                  <!-- Formulár na pridanie nového dielu -->
                  <div v-if="showAddPartForm" class="card">
                    <div class="card-body">
                      <h6 class="card-subtitle mb-3">Pridať diel</h6>
                      <form @submit.prevent="addPart">
                        <div class="mb-3">
                          <label for="partName" class="form-label">Názov dielu</label>
                          <input
                            type="text"
                            id="partName"
                            class="form-control"
                            v-model="newPartForm.name"
                            required
                          />
                        </div>
                        <div class="mb-3">
                          <label for="serialNumber" class="form-label">Sériové číslo</label>
                          <input
                            type="text"
                            id="serialNumber"
                            class="form-control"
                            v-model="newPartForm.serialnumber"
                            required
                          />
                        </div>
                        <div class="d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary">Pridať diel</button>
                          <button type="button" class="btn btn-link" @click="toggleAddPartForm">
                            Zrušiť
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
  
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'CarsComponent',
    data() {
      return {
        cars: [],
        showForm: false,
        editingCar: null,
        form: {
          name: '',
          is_registered: false,
          registration_number: ''
        },
        // Filter pre načítanie áut
        currentFilter: '',
        // Premenné pre rozbalenie dielov pre konkrétne auto
        expandedCarId: null,
        selectedCarParts: [],
        // Filter pre diely – prázdny znamená predvolené zoradenie (napr. latest), 'alphabetically' pre abecedné zoradenie
        partsFilter: '',
        // Formulár na pridanie nového dielu – použitie názvu a serialnumber
        newPartForm: {
          name: '',
          serialnumber: ''
        },
        // Stav zobrazenia formulára na pridanie dielu (v rozbalenom riadku)
        showAddPartForm: false,
        // Premenné pre inline editáciu dielov
        editingPartId: null,
        partForm: {
          name: '',
          serialnumber: ''
        }
      };
    },
    mounted() {
      this.getCars();
    },
    methods: {
      // Načíta zoznam áut z API s prípadným filtrom
      getCars() {
        axios
          .get('api/cars', { params: { filter: this.currentFilter } })
          .then((response) => {
            this.cars = response.data.cars;
          })
          .catch((error) => {
            console.error('Chyba pri načítavaní áut:', error);
          });
      },
      // Aplikácia filtra a opätovné načítanie áut
      applyFilter(filter) {
        this.currentFilter = filter;
        this.getCars();
      },
      // Prepína zobrazenie formulára pre auto
      toggleForm() {
        this.showForm = !this.showForm;
        if (!this.showForm) {
          this.resetForm();
        }
      },
      // Odoslanie formulára pre vytvorenie alebo aktualizáciu auta
      submitForm() {
        if (this.form.is_registered && !this.form.registration_number) {
          alert('Registračné číslo je povinné, keď je auto registrované.');
          return;
        }
    
        if (this.editingCar) {
          axios
            .put(`api/cars/${this.editingCar.id}`, this.form)
            .then(() => {
              this.getCars();
              this.resetForm();
            })
            .catch((error) => {
              console.error('Chyba pri aktualizácii auta:', error);
            });
        } else {
          axios
            .post('api/cars', this.form)
            .then(() => {
              this.getCars();
              this.resetForm();
            })
            .catch((error) => {
              console.error('Chyba pri pridávaní auta:', error);
            });
        }
      },
      // Načíta údaje auta do formulára pre úpravu
      editCar(car) {
        this.editingCar = car;
        this.form = {
          name: car.name,
          is_registered: Boolean(car.is_registered),
          registration_number: car.registration_number || ''
        };
        this.showForm = true;
        if (this.expandedCarId === car.id) {
          this.expandedCarId = null;
          this.selectedCarParts = [];
          this.showAddPartForm = false;
        }
      },
      // Vymazanie auta
      deleteCar(car) {
        if (confirm('Ste si istí, že chcete vymazať toto auto?')) {
          axios
            .delete(`api/cars/${car.id}`)
            .then(() => {
              this.getCars();
              if (this.expandedCarId === car.id) {
                this.expandedCarId = null;
                this.selectedCarParts = [];
                this.showAddPartForm = false;
              }
            })
            .catch((error) => {
              console.error('Chyba pri vymazávaní auta:', error);
            });
        }
      },
      // Rozbaľovanie a zbalovanie dielov pre dané auto
      showParts(car) {
        if (this.expandedCarId === car.id) {
          this.expandedCarId = null;
          this.selectedCarParts = [];
          this.showAddPartForm = false;
        } else {
          axios
            .get(`api/cars/${car.id}`, { params: { parts_filter: this.partsFilter } })
            .then((response) => {
              if (response.data.car) {
                this.expandedCarId = car.id;
                this.selectedCarParts = response.data.car.parts;
                this.showAddPartForm = false;
              } else {
                console.error('Diely neboli načítané. Skontrolujte API odpoveď.');
              }
            })
            .catch((error) => {
              console.error('Chyba pri načítavaní dielov:', error);
            });
        }
      },
      // Metóda na načítanie dielov pre aktuálne rozbalené auto (s aktuálnym partsFilter)
      reloadParts() {
        if (this.expandedCarId) {
          axios
            .get(`api/cars/${this.expandedCarId}`, { params: { parts_filter: this.partsFilter } })
            .then((response) => {
              if (response.data.car) {
                this.selectedCarParts = response.data.car.parts;
              }
            })
            .catch((error) => {
              console.error('Chyba pri načítavaní dielov:', error);
            });
        }
      },
      // Prepína zobrazenie tlačidla na abecedné zoradenie dielov
      togglePartsFilter() {
        // Ak je aktuálny filter abecedný, zrušíme ho (nastavíme prázdny reťazec), inak nastavíme 'alphabetically'
        this.partsFilter = this.partsFilter === 'alphabetically' ? '' : 'alphabetically';
        this.reloadParts();
      },
      // Prepína zobrazenie formulára na pridanie dielu
      toggleAddPartForm() {
        this.showAddPartForm = !this.showAddPartForm;
        if (!this.showAddPartForm) {
          this.newPartForm = { name: '', serialnumber: '' };
        }
      },
      // Pridá nový diel ku vybranému autu
      addPart() {
        if (!this.newPartForm.name || !this.newPartForm.serialnumber) {
          alert('Zadajte názov dielu aj sériové číslo.');
          return;
        }
        axios
          .post(`api/cars/${this.expandedCarId}/parts`, this.newPartForm)
          .then((response) => {
            this.selectedCarParts.push(response.data.part);
            this.newPartForm = { name: '', serialnumber: '' };
            this.showAddPartForm = false;
          })
          .catch((error) => {
            console.error('Chyba pri pridávaní dielu:', error);
          });
      },
      // Inline editácia dielov – nastavenie editovacieho režimu pre daný diel
      editPart(part) {
        this.editingPartId = part.id;
        this.partForm = {
          name: part.name,
          serialnumber: part.serialnumber
        };
      },
      // Odoslanie aktualizovaného dielu na API
      updatePart(part) {
        axios
          .put(`api/cars/${this.expandedCarId}/parts/${part.id}`, this.partForm)
          .then((response) => {
            const updatedPart = response.data.part;
            const index = this.selectedCarParts.findIndex((p) => p.id === part.id);
            if (index !== -1) {
              this.selectedCarParts.splice(index, 1, updatedPart);
            }
            this.editingPartId = null;
            this.partForm = { name: '', serialnumber: '' };
          })
          .catch((error) => {
            console.error('Chyba pri aktualizácii dielu:', error);
          });
      },
      // Zrušenie inline editácie dielu
      cancelEditPart() {
        this.editingPartId = null;
        this.partForm = { name: '', serialnumber: '' };
      },
      // Vymazanie dielu
      deletePart(part) {
        if (confirm('Ste si istí, že chcete vymazať tento diel?')) {
          axios
            .delete(`api/parts/${part.id}`)
            .then(() => {
              this.selectedCarParts = this.selectedCarParts.filter((p) => p.id !== part.id);
            })
            .catch((error) => {
              console.error('Chyba pri vymazávaní dielu:', error);
            });
        }
      },
      // Resetuje formulár pre auto
      resetForm() {
        this.form = {
          name: '',
          is_registered: false,
          registration_number: ''
        };
        this.editingCar = null;
        this.showForm = false;
      }
    }
  };
  </script>
  
  <style scoped>
  .table-hover tbody tr:hover {
    background-color: #f2f2f2;
  }
  .card {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  .border {
    border: 1px solid #dee2e6 !important;
  }
  .bg-light {
    background-color: #f8f9fa !important;
  }
  </style>
  