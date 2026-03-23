// Current selection & global maps
let currentBuilding = 'A';
let currentFloor = 1;
let currentRoom = null;
let buildings = {};
let statuses = [];
let dbUnitStatusMap = {}; // unit_no => status

async function fetchBuildings() {
  // Simulate API call to fetch buildings from DB
  // In real implementation: return await fetch('/api/buildings').then(res => res.json());
  return {
    'A': {
      name: 'Building A',
      floors: [
        {
          floor: 1,
          floorPlan: "/images/floorplan/a1.png",
          rooms: [
            { unit_no: '1', position: { top: '43%', left: '23%' }},
            { unit_no: '2', position: { top: '47.5%', left: '28.5%' }},
            { unit_no: '3', position: { top: '52%', left: '34%' }},
            { unit_no: '4', position: { top: '57%', left: '39.5%' }},
            { unit_no: '5', position: { top: '62.5%', left: '46%' }},
            { unit_no: '6', position: { top: '65%', left: '54%' }},
            { unit_no: '7', position: { top: '65%', left: '59.8%' }},
            { unit_no: '8', position: { top: '65%', left: '67.3%' }},
            { unit_no: '9', position: { top: '56%', left: '74.5%' }},
            { unit_no: '10', position: { top: '48%', left: '74.5%' }},
            { unit_no: '11', position: { top: '40%', left: '74.5%' }},
            { unit_no: '12', position: { top: '42%', left: '83.5%' }},
            { unit_no: '13', position: { top: '49.5%', left: '83.5%' }},
            { unit_no: '14', position: { top: '57%', left: '83.5%' }},
            { unit_no: '15', position: { top: '66%', left: '83.5%' }},
            { unit_no: '16', position: { top: '79%', left: '83.6%' }},
            { unit_no: '17', position: { top: '79%', left: '77.5%' }},
            { unit_no: '18', position: { top: '79%', left: '72%' }},
            { unit_no: '19', position: { top: '79%', left: '66%' }},
            { unit_no: '20', position: { top: '79%', left: '60%' }},
            { unit_no: '21', position: { top: '79%', left: '53.5%' }},
            { unit_no: '22', position: { top: '79%', left: '46.2%' }},
            { unit_no: '23', position: { top: '73.8%', left: '40%' }},
            { unit_no: '24', position: { top: '69%', left: '34.5%' }},
            { unit_no: '25', position: { top: '64.5%', left: '29%' }},
            { unit_no: '26', position: { top: '57.5%', left: '20.5%' }},
            { unit_no: '27', position: { top: '52.5%', left: '14%' }}, 
          ],
        },
        {
          floor: 2,
          floorPlan: "/images/floorplan/a2.png",
          rooms: [
            { unit_no: '1', position: { top: '43%', left: '23%' }},
            { unit_no: '2', position: { top: '47.5%', left: '28.5%' }},
            { unit_no: '3', position: { top: '52%', left: '34%' }},
            { unit_no: '4', position: { top: '57%', left: '39.5%' }},
            { unit_no: '5', position: { top: '62.5%', left: '46%' }},
            { unit_no: '6', position: { top: '65%', left: '54%' }},
            { unit_no: '7', position: { top: '65%', left: '59.8%' }},
            { unit_no: '8', position: { top: '65%', left: '67.3%' }},
            { unit_no: '9', position: { top: '56%', left: '74.5%' }},
            { unit_no: '10', position: { top: '48%', left: '74.5%' }},
            { unit_no: '11', position: { top: '40%', left: '74.5%' }},
            { unit_no: '12', position: { top: '31%', left: '73.5%' }}, 
            { unit_no: '13', position: { top: '23.5%', left: '72.5%' }}, 
            { unit_no: '14', position: { top: '14.8%', left: '71%' }}, 
            { unit_no: '15', position: { top: '13.5%', left: '79.5%' }},
            { unit_no: '16', position: { top: '38.6%', left: '83%' }},
            { unit_no: '17', position: { top: '49.5%', left: '83.5%' }},
            { unit_no: '18', position: { top: '57%', left: '83.5%' }},
            { unit_no: '19', position: { top: '66%', left: '83.5%' }},
            { unit_no: '20', position: { top: '79%', left: '83.6%' }},
            { unit_no: '21', position: { top: '79%', left: '77.5%' }},
            { unit_no: '22', position: { top: '79%', left: '72%' }},
            { unit_no: '23', position: { top: '79%', left: '65.5%' }},
            { unit_no: '24', position: { top: '79%', left: '60%' }},
            { unit_no: '25', position: { top: '79%', left: '53.5%' }},
            { unit_no: '26', position: { top: '79%', left: '46.2%' }},
            { unit_no: '27', position: { top: '73.8%', left: '40%' }},
            { unit_no: '28', position: { top: '69%', left: '34.5%' }},
            { unit_no: '29', position: { top: '64.6%', left: '30%' }},
            { unit_no: '30', position: { top: '57.5%', left: '20.5%' }},
            { unit_no: '31', position: { top: '52.5%', left: '14%' }},
          ],
        },
        {
          floor: 3,
          floorPlan: "/images/floorplan/a3.png",
          rooms: [
            { unit_no: '1', position: { top: '43%', left: '23%' }},
            { unit_no: '2', position: { top: '47.5%', left: '28.5%' }},
            { unit_no: '3', position: { top: '52%', left: '34%' }},
            { unit_no: '4', position: { top: '57%', left: '39.5%' }},
            { unit_no: '5', position: { top: '62.5%', left: '46%' }},
            { unit_no: '6', position: { top: '65%', left: '54%' }},
            { unit_no: '7', position: { top: '65%', left: '59.8%' }},
            { unit_no: '8', position: { top: '65%', left: '67.3%' }},
            { unit_no: '9', position: { top: '56%', left: '74.5%' }},
            { unit_no: '10', position: { top: '48%', left: '74.5%' }},
            { unit_no: '11', position: { top: '40%', left: '74.5%' }},
            { unit_no: '12', position: { top: '31%', left: '73.5%' }}, 
            { unit_no: '13', position: { top: '23.5%', left: '72.5%' }}, 
            { unit_no: '14', position: { top: '14.8%', left: '71%' }}, 
            { unit_no: '15', position: { top: '13.5%', left: '79.5%' }},
            { unit_no: '16', position: { top: '38.6%', left: '83%' }},
            { unit_no: '17', position: { top: '49.5%', left: '83.5%' }},
            { unit_no: '18', position: { top: '57%', left: '83.5%' }},
            { unit_no: '19', position: { top: '66%', left: '83.5%' }},
            { unit_no: '20', position: { top: '79%', left: '83.6%' }},
            { unit_no: '21', position: { top: '79%', left: '76.2%' }},
            { unit_no: '22', position: { top: '79%', left: '72%' }},
            { unit_no: '23', position: { top: '79%', left: '65.5%' }},
            { unit_no: '24', position: { top: '79%', left: '60%' }},
            { unit_no: '25', position: { top: '79%', left: '53.5%' }},
            { unit_no: '26', position: { top: '79%', left: '46.2%' }},
            { unit_no: '27', position: { top: '73.8%', left: '40%' }},
            { unit_no: '28', position: { top: '69%', left: '34.5%' }},
            { unit_no: '29', position: { top: '64.6%', left: '30%' }},
            { unit_no: '30', position: { top: '57.5%', left: '20.5%' }},
            { unit_no: '31', position: { top: '52.5%', left: '14%' }},
          ],
        },
        {
          floor: 4,
          floorPlan: "/images/floorplan/a4.png",
          rooms: [
            { unit_no: '1', position: { top: '43%', left: '23%' }},
            { unit_no: '2', position: { top: '47.5%', left: '28.5%' }},
            { unit_no: '3', position: { top: '52%', left: '34%' }},
            { unit_no: '4', position: { top: '57%', left: '39.5%' }},
            { unit_no: '5', position: { top: '62.5%', left: '46%' }},
            { unit_no: '6', position: { top: '65%', left: '54%' }},
            { unit_no: '7', position: { top: '65%', left: '59.8%' }},
            { unit_no: '8', position: { top: '65%', left: '67.3%' }},
            { unit_no: '9', position: { top: '56%', left: '74.5%' }},
            { unit_no: '10', position: { top: '48%', left: '74.5%' }},
            { unit_no: '11', position: { top: '40%', left: '74.5%' }},
            { unit_no: '12', position: { top: '31%', left: '73.5%' }}, 
            { unit_no: '13', position: { top: '23.5%', left: '72.5%' }}, 
            { unit_no: '14', position: { top: '14.8%', left: '71%' }}, 
            { unit_no: '15', position: { top: '13.5%', left: '79.5%' }},
            { unit_no: '16', position: { top: '38.6%', left: '83%' }},
            { unit_no: '17', position: { top: '49.5%', left: '83.5%' }},
            { unit_no: '18', position: { top: '57%', left: '83.5%' }},
            { unit_no: '19', position: { top: '66%', left: '83.5%' }},
            { unit_no: '20', position: { top: '79%', left: '83.6%' }},
            { unit_no: '21', position: { top: '79%', left: '77.5%' }},
            { unit_no: '22', position: { top: '79%', left: '72%' }},
            { unit_no: '23', position: { top: '79%', left: '65.5%' }},
            { unit_no: '24', position: { top: '79%', left: '60%' }},
            { unit_no: '25', position: { top: '79%', left: '53.5%' }},
            { unit_no: '26', position: { top: '79%', left: '46.2%' }},
            { unit_no: '27', position: { top: '73.8%', left: '40%' }},
            { unit_no: '28', position: { top: '69%', left: '34.5%' }},
            { unit_no: '29', position: { top: '64.6%', left: '30%' }},
            { unit_no: '30', position: { top: '57.5%', left: '20.5%' }},
            { unit_no: '31', position: { top: '52.5%', left: '14%' }},
          ],
        },
      ],
    },
    'B': {
      name: 'Building B',
      floors: [
        {
          floor: 1,
          floorPlan: "/images/floorplan/b1.png",
          rooms: [
            { unit_no: '1', position: { top: '31%', left: '28%' }},
            { unit_no: '2', position: { top: '36.7%', left: '28.7%' }},
            { unit_no: '3', position: { top: '42.5%', left: '29.4%' }},
            { unit_no: '4', position: { top: '48.4%', left: '30%' }},
            { unit_no: '5', position: { top: '54%', left: '30.7%' }},
            { unit_no: '6', position: { top: '59.6%', left: '31.4%' }},
            { unit_no: '7', position: { top: '67%', left: '37.2%' }},
            { unit_no: '8', position: { top: '67%', left: '43.5%' }},
            { unit_no: '9', position: { top: '67%', left: '50.8%' }},
            { unit_no: '10', position: { top: '67%', left: '58.4%' }},
            { unit_no: '11', position: { top: '50.6%', left: '75.4%' }},
            { unit_no: '12', position: { top: '56.2%', left: '77%' }},
            { unit_no: '13', position: { top: '61.4%', left: '78.6%' }},
            { unit_no: '14', position: { top: '67%', left: '80.4%' }},
            { unit_no: '15', position: { top: '72.2%', left: '81.9%' }},
            { unit_no: '16', position: { top: '78.8%', left: '75.3%' }},
            { unit_no: '17', position: { top: '78.8%', left: '71.2%' }},
            { unit_no: '18', position: { top: '78.8%', left: '67.5%' }},
            { unit_no: '19', position: { top: '78.8%', left: '63.5%' }},
            { unit_no: '20', position: { top: '78.8%', left: '59.5%' }},
            { unit_no: '21', position: { top: '78.8%', left: '55.7%' }},
            { unit_no: '22', position: { top: '78.8%', left: '51.7%' }},
            { unit_no: '23', position: { top: '78.8%', left: '47.8%' }},
            { unit_no: '24', position: { top: '78.8%', left: '44%' }},
            { unit_no: '25', position: { top: '78.8%', left: '40%' }},
            { unit_no: '26', position: { top: '78.8%', left: '36%' }},
            { unit_no: '27', position: { top: '78.8%', left: '32%' }},
            { unit_no: '28', position: { top: '78.8%', left: '25.6%' }},
            { unit_no: '29', position: { top: '73.2%', left: '25%' }},
            { unit_no: '30', position: { top: '67.5%', left: '24.3%' }},
            { unit_no: '31', position: { top: '61.6%', left: '23.6%' }},
            { unit_no: '32', position: { top: '55.8%', left: '23%' }},
            { unit_no: '33', position: { top: '50.2%', left: '22.3%' }},
            { unit_no: '34', position: { top: '44.2%', left: '21.5%' }},
            { unit_no: '35', position: { top: '38.8%', left: '20.8%' }},
            { unit_no: '36', position: { top: '32.8%', left: '20%' }},
          ],
        },
        {
          floor: 2,
          floorPlan: "/images/floorplan/b2.png",
          rooms: [
            { unit_no: '1', position: { top: '31%', left: '28%' }},
            { unit_no: '2', position: { top: '36.7%', left: '28.7%' }},
            { unit_no: '3', position: { top: '42.5%', left: '29.4%' }},
            { unit_no: '4', position: { top: '48.4%', left: '30%' }},
            { unit_no: '5', position: { top: '54%', left: '30.7%' }},
            { unit_no: '6', position: { top: '59.6%', left: '31.4%' }},
            { unit_no: '7', position: { top: '67%', left: '37.2%' }},
            { unit_no: '8', position: { top: '67%', left: '43.5%' }},
            { unit_no: '9', position: { top: '67%', left: '50.8%' }},
            { unit_no: '10', position: { top: '67%', left: '58.4%' }},
            { unit_no: '11', position: { top: '56.8%', left: '68.4%' }},
            { unit_no: '12', position: { top: '51.5%', left: '67%' }},
            { unit_no: '13', position: { top: '46.2%', left: '65.5%' }},
            { unit_no: '14', position: { top: '41%', left: '64%' }},
            { unit_no: '15', position: { top: '35.6%', left: '62.5%' }},
            { unit_no: '16', position: { top: '29%', left: '60.5%' }},
            { unit_no: '17', position: { top: '18.7%', left: '65.7%' }},
            { unit_no: '18', position: { top: '24%', left: '67.4%' }},
            { unit_no: '19', position: { top: '29.5%', left: '69.1%' }},
            { unit_no: '20', position: { top: '34.5%', left: '70.6%' }},
            { unit_no: '21', position: { top: '40%', left: '72.2%' }},
            { unit_no: '22', position: { top: '45.4%', left: '73.8%' }},
            { unit_no: '23', position: { top: '50.6%', left: '75.4%' }},
            { unit_no: '24', position: { top: '56.2%', left: '77%' }},
            { unit_no: '25', position: { top: '61.4%', left: '78.6%' }},
            { unit_no: '26', position: { top: '67%', left: '80.4%' }},
            { unit_no: '27', position: { top: '72.2%', left: '81.9%' }},
            { unit_no: '28', position: { top: '78%', left: '83.6%' }},
            { unit_no: '29', position: { top: '78.8%', left: '75.3%' }},
            { unit_no: '30', position: { top: '78.8%', left: '71.2%' }},
            { unit_no: '31', position: { top: '78.8%', left: '67.7%' }},
            { unit_no: '32', position: { top: '78.8%', left: '63.5%' }},
            { unit_no: '33', position: { top: '78.8%', left: '59.5%' }},
            { unit_no: '34', position: { top: '78.8%', left: '55.7%' }},
            { unit_no: '35', position: { top: '78.8%', left: '51.7%' }},
            { unit_no: '36', position: { top: '78.8%', left: '47.8%' }},
            { unit_no: '37', position: { top: '78.8%', left: '44%' }},
            { unit_no: '38', position: { top: '78.8%', left: '40%' }},
            { unit_no: '39', position: { top: '78.8%', left: '36%' }},
            { unit_no: '40', position: { top: '78.8%', left: '32%' }},
            { unit_no: '41', position: { top: '78.8%', left: '25.6%' }},
            { unit_no: '42', position: { top: '73.2%', left: '25%' }},            
            { unit_no: '43', position: { top: '67.5%', left: '24.3%' }},
            { unit_no: '44', position: { top: '61.6%', left: '23.6%' }},
            { unit_no: '45', position: { top: '55.8%', left: '23%' }},
            { unit_no: '46', position: { top: '50.2%', left: '22.3%' }},
            { unit_no: '47', position: { top: '44.2%', left: '21.5%' }},
            { unit_no: '48', position: { top: '38.8%', left: '20.8%' }},
            { unit_no: '49', position: { top: '32.8%', left: '20%' }},
          ],
        },
        {
          floor: 3,
          floorPlan: "/images/floorplan/b3.png",
          rooms: [
            { unit_no: '1', position: { top: '31%', left: '28%' }},
            { unit_no: '2', position: { top: '36.7%', left: '28.7%' }},
            { unit_no: '3', position: { top: '42.5%', left: '29.4%' }},
            { unit_no: '4', position: { top: '48.4%', left: '30%' }},
            { unit_no: '5', position: { top: '54%', left: '30.7%' }},
            { unit_no: '6', position: { top: '59.6%', left: '31.4%' }},
            { unit_no: '7', position: { top: '67%', left: '37.2%' }},
            { unit_no: '8', position: { top: '67%', left: '43.5%' }},
            { unit_no: '9', position: { top: '67%', left: '50.8%' }},
            { unit_no: '10', position: { top: '67%', left: '58.4%' }},
            { unit_no: '11', position: { top: '56.8%', left: '68.4%' }},
            { unit_no: '12', position: { top: '51.5%', left: '67%' }},
            { unit_no: '13', position: { top: '46.2%', left: '65.5%' }},
            { unit_no: '14', position: { top: '41%', left: '64%' }},
            { unit_no: '15', position: { top: '35.6%', left: '62.5%' }},
            { unit_no: '16', position: { top: '29%', left: '60.5%' }},
            { unit_no: '17', position: { top: '18.7%', left: '65.7%' }},
            { unit_no: '18', position: { top: '24%', left: '67.4%' }},
            { unit_no: '19', position: { top: '29.5%', left: '69.1%' }},
            { unit_no: '20', position: { top: '34.5%', left: '70.6%' }},
            { unit_no: '21', position: { top: '40%', left: '72.2%' }},
            { unit_no: '22', position: { top: '45.4%', left: '73.8%' }},
            { unit_no: '23', position: { top: '50.6%', left: '75.4%' }},
            { unit_no: '24', position: { top: '56.2%', left: '77%' }},
            { unit_no: '25', position: { top: '61.4%', left: '78.6%' }},
            { unit_no: '26', position: { top: '67%', left: '80.4%' }},
            { unit_no: '27', position: { top: '72.2%', left: '81.9%' }},
            { unit_no: '28', position: { top: '78%', left: '83.6%' }},
            { unit_no: '29', position: { top: '78.8%', left: '75.3%' }},
            { unit_no: '30', position: { top: '78.8%', left: '71.2%' }},
            { unit_no: '31', position: { top: '78.8%', left: '67.7%' }},
            { unit_no: '32', position: { top: '78.8%', left: '63.5%' }},
            { unit_no: '33', position: { top: '78.8%', left: '59.5%' }},
            { unit_no: '34', position: { top: '78.8%', left: '55.7%' }},
            { unit_no: '35', position: { top: '78.8%', left: '51.7%' }},
            { unit_no: '36', position: { top: '78.8%', left: '47.8%' }},
            { unit_no: '37', position: { top: '78.8%', left: '44%' }},
            { unit_no: '38', position: { top: '78.8%', left: '40%' }},
            { unit_no: '39', position: { top: '78.8%', left: '36%' }},
            { unit_no: '40', position: { top: '78.8%', left: '32%' }},
            { unit_no: '41', position: { top: '78.8%', left: '25.6%' }},
            { unit_no: '42', position: { top: '73.2%', left: '25%' }},            
            { unit_no: '43', position: { top: '67.5%', left: '24.3%' }},
            { unit_no: '44', position: { top: '61.6%', left: '23.6%' }},
            { unit_no: '45', position: { top: '55.8%', left: '23%' }},
            { unit_no: '46', position: { top: '50.2%', left: '22.3%' }},
            { unit_no: '47', position: { top: '44.2%', left: '21.5%' }},
            { unit_no: '48', position: { top: '38.8%', left: '20.8%' }},
            { unit_no: '49', position: { top: '32.8%', left: '20%' }},
          ],
        },
        {
          floor: 4,
          floorPlan: "/images/floorplan/b4.png",
          rooms: [
            { unit_no: '1', position: { top: '31%', left: '28%' }},
            { unit_no: '2', position: { top: '36.7%', left: '28.7%' }},
            { unit_no: '3', position: { top: '42.5%', left: '29.4%' }},
            { unit_no: '4', position: { top: '48.4%', left: '30%' }},
            { unit_no: '5', position: { top: '54%', left: '30.7%' }},
            { unit_no: '6', position: { top: '59.6%', left: '31.4%' }},
            { unit_no: '7', position: { top: '67%', left: '37.2%' }},
            { unit_no: '8', position: { top: '67%', left: '43.5%' }},
            { unit_no: '9', position: { top: '67%', left: '50.8%' }},
            { unit_no: '10', position: { top: '67%', left: '58.4%' }},
            { unit_no: '11', position: { top: '56.8%', left: '68.4%' }},
            { unit_no: '12', position: { top: '51.5%', left: '67%' }},
            { unit_no: '13', position: { top: '46.2%', left: '65.5%' }},
            { unit_no: '14', position: { top: '41%', left: '64%' }},
            { unit_no: '15', position: { top: '35.6%', left: '62.5%' }},
            { unit_no: '16', position: { top: '29%', left: '60.5%' }},
            { unit_no: '17', position: { top: '18.7%', left: '65.7%' }},
            { unit_no: '18', position: { top: '24%', left: '67.4%' }},
            { unit_no: '19', position: { top: '29.5%', left: '69.1%' }},
            { unit_no: '20', position: { top: '34.5%', left: '70.6%' }},
            { unit_no: '21', position: { top: '40%', left: '72.2%' }},
            { unit_no: '22', position: { top: '45.4%', left: '73.8%' }},
            { unit_no: '23', position: { top: '50.6%', left: '75.4%' }},
            { unit_no: '24', position: { top: '56.2%', left: '77%' }},
            { unit_no: '25', position: { top: '61.4%', left: '78.6%' }},
            { unit_no: '26', position: { top: '67%', left: '80.4%' }},
            { unit_no: '27', position: { top: '72.2%', left: '81.9%' }},
            { unit_no: '28', position: { top: '78%', left: '83.6%' }},
            { unit_no: '29', position: { top: '78.8%', left: '75.3%' }},
            { unit_no: '30', position: { top: '78.8%', left: '71.2%' }},
            { unit_no: '31', position: { top: '78.8%', left: '67.7%' }},
            { unit_no: '32', position: { top: '78.8%', left: '63.5%' }},
            { unit_no: '33', position: { top: '78.8%', left: '59.5%' }},
            { unit_no: '34', position: { top: '78.8%', left: '55.7%' }},
            { unit_no: '35', position: { top: '78.8%', left: '51.7%' }},
            { unit_no: '36', position: { top: '78.8%', left: '47.8%' }},
            { unit_no: '37', position: { top: '78.8%', left: '44%' }},
            { unit_no: '38', position: { top: '78.8%', left: '40%' }},
            { unit_no: '39', position: { top: '78.8%', left: '36%' }},
            { unit_no: '40', position: { top: '78.8%', left: '32%' }},
            { unit_no: '41', position: { top: '78.8%', left: '25.6%' }},
            { unit_no: '42', position: { top: '73.2%', left: '25%' }},            
            { unit_no: '43', position: { top: '67.5%', left: '24.3%' }},
            { unit_no: '44', position: { top: '61.6%', left: '23.6%' }},
            { unit_no: '45', position: { top: '55.8%', left: '23%' }},
            { unit_no: '46', position: { top: '50.2%', left: '22.3%' }},
            { unit_no: '47', position: { top: '44.2%', left: '21.5%' }},
            { unit_no: '48', position: { top: '38.8%', left: '20.8%' }},
            { unit_no: '49', position: { top: '32.8%', left: '20%' }},
          ],
        },
      ],
    },
    'C': {
      name: 'Building C',
      floors: [
        {
          floor: 1,
          floorPlan: "/images/floorplan/c1.png",
          rooms: [
            { unit_no: '1', position: { top: '52.5%', left: '49%' }},
            { unit_no: '2', position: { top: '52.5%', left: '38%' }},
            { unit_no: '3', position: { top: '52.5%', left: '30.5%' }},
            { unit_no: '4', position: { top: '52.5%', left: '20%' }},          
        ],
        },
        {
          floor: 2,
          floorPlan: "/images/floorplan/c2.png",
          rooms: [
            { unit_no: '1', position: { top: '52%', left: '12.1%' }},
            { unit_no: '2', position: { top: '52%', left: '25.5%' }},
            { unit_no: '3', position: { top: '52%', left: '34.5%' }},
            { unit_no: '4', position: { top: '52%', left: '43.5%' }},
            { unit_no: '5', position: { top: '52%', left: '53%' }},
            { unit_no: '6', position: { top: '52%', left: '74.5%' }},
        ],
        },
        {
          floor: 3,
          floorPlan: "/images/floorplan/c3.png",
          rooms: [
            { unit_no: '1', position: { top: '52%', left: '12%' }},
            { unit_no: '2', position: { top: '52%', left: '25%' }},
            { unit_no: '3', position: { top: '52%', left: '34.5%' }},
            { unit_no: '4', position: { top: '52%', left: '44%' }},
            { unit_no: '5', position: { top: '52%', left: '53%' }},
            { unit_no: '6', position: { top: '52%', left: '74.5%' }},
            { unit_no: '7', position: { top: '52%', left: '80.5%' }},
            { unit_no: '8', position: { top: '52%', left: '87.5%' }},
          ],
        },
      ],
    },
  };
}
async function fetchStatuses() {
  try {
    const res = await fetch('/api/floorplan', {
      headers: { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Failed to fetch statuses');

    const data = await res.json();
    dbUnitStatusMap = data.units || {};
    // Normalize keys to string to avoid number-vs-string mismatches
    const normalized = {};
    Object.keys(dbUnitStatusMap || {}).forEach(k => {
      normalized[String(k)] = dbUnitStatusMap[k];
    });
    dbUnitStatusMap = normalized;
    console.debug('DB unit status map', dbUnitStatusMap);

    // Server returns process statuses; FE can keep a list but not used for selection anymore
    return data.statuses || ['Available', 'Reserved', 'Contract', 'Installment', 'Transferred'];
  } catch (e) {
    console.warn('Fallback to default statuses:', e);
    dbUnitStatusMap = dbUnitStatusMap || {};
    return ['Request', 'Opening', 'Appointment', 'Booking', 'Closing', 'Available'];
  }
}

// -------------------- Process Status Helpers (read-only) --------------------
function categorizeProcessStatus(raw) {
  // Returns one of: 'available', 'reserved', 'contract', 'installment', 'transferred'
  const s = String(raw || '').toLowerCase();
  if (s === 'transferred') return 'transferred';
  if (s === 'installment') return 'installment';
  if (s === 'contract') return 'contract';
  if (s === 'reserved') return 'reserved';
  return 'available';
}

// -------------------- Load Data --------------------
async function loadData() {
  buildings = await fetchBuildings();
  statuses = await fetchStatuses();
  updateFloorTabs();
  updateDisplay();
}


// -------------------- Floor Select --------------------
function updateFloorTabs() {
  const floorSelect = document.getElementById('floorSelect');
  floorSelect.innerHTML = '';
  if (!buildings || !buildings[currentBuilding]) return;
  const building = buildings[currentBuilding];
  building.floors.forEach(floor => {
    const option = document.createElement('option');
    option.value = floor.floor;
    option.textContent = `Floor ${floor.floor}`;
    if (floor.floor === currentFloor) option.selected = true;
    floorSelect.appendChild(option);
  });
}

// Attach floor select change event
document.addEventListener('DOMContentLoaded', function () {
  const floorSelect = document.getElementById('floorSelect');
  if (floorSelect) {
    floorSelect.addEventListener('change', function () {
      selectFloor(parseInt(this.value, 10));
    });
  }
});

function selectFloor(floor) {
  currentFloor = floor;
  const floorSelect = document.getElementById('floorSelect');
  if (floorSelect) floorSelect.value = floor;
  updateDisplay();
}

// -------------------- Building Selection --------------------
function selectBuilding(letter) {
  const b = String(letter || '').toUpperCase();
  currentBuilding = b;
  if (!buildings || !buildings[b]) return;
  // Reset to first floor of this building
  const firstFloor = (buildings[b].floors[0] || {}).floor || 1;
  currentFloor = firstFloor;
  // Sync building select
  const buildingSelect = document.getElementById('buildingSelect');
  if (buildingSelect) buildingSelect.value = b;
  updateFloorTabs();
  updateDisplay();
}

// -------------------- Display --------------------
function updateDisplay() {
  const building = buildings[currentBuilding] || buildings['A'];
  const floor = building.floors.find(f => f.floor === currentFloor);
  if (!floor) return;

  const fullName = `${building.name} · Floor ${currentFloor}`;
  document.getElementById('floorInfo').innerHTML = fullName;
  document.getElementById('contentTitle').innerHTML = fullName;

  // Helper to compute project_name e.g., A1-01 (Building A, floor 1, unit 1 -> 01)
  const computeProjectName = (bld, fl, unitNo) => {
    const unitSegment = String(unitNo).padStart(2, '0');
    return `${bld}${fl}${unitSegment}`;
  };

  const displayRooms = floor.rooms.map(r => ({
    ...r,
    project_name: computeProjectName(currentBuilding, currentFloor, r.unit_no),
    status: dbUnitStatusMap[computeProjectName(currentBuilding, currentFloor, r.unit_no)] || 'Available',
    category: categorizeProcessStatus(dbUnitStatusMap[computeProjectName(currentBuilding, currentFloor, r.unit_no)])
  }));

  // Debug: show how many unit_nos matched DB statuses
  try {
    const roomKeys = displayRooms.map(r => r.project_name);
    const matched = roomKeys.filter(k => Object.prototype.hasOwnProperty.call(dbUnitStatusMap, k));
    const missing = roomKeys.filter(k => !Object.prototype.hasOwnProperty.call(dbUnitStatusMap, k));
    console.debug('[Floorplan] matched units:', matched);
    if (missing.length) console.warn('[Floorplan] missing units (no DB status found, defaulted to Available):', missing);
  } catch (_) { /* noop */ }

  // Update status counters (5 statuses)
  try {
    const counts = { available: 0, reserved: 0, contract: 0, installment: 0, transferred: 0 };
    displayRooms.forEach(r => {
      if (counts[r.category] !== undefined) counts[r.category] += 1;
      else counts.available += 1;
    });
    const ids = ['available', 'reserved', 'contract', 'installment', 'transferred'];
    ids.forEach(key => {
      const el = document.getElementById(key + 'Count');
      if (el) el.textContent = counts[key];
    });
  } catch (_) { /* noop */ }

  const hotspotContainer = document.getElementById('hotspotContainer');
  hotspotContainer.style.backgroundImage = `url(${floor.floorPlan})`;
  hotspotContainer.innerHTML = '';

  displayRooms.forEach(room => {
    const statusClass = room.category; // available | reserved | contract | installment | transferred
    const hotspot = document.createElement('div');
    hotspot.className = `hotspot ${statusClass}`;
    hotspot.textContent = room.unit_no;
    hotspot.style.top = room.position.top;
    hotspot.style.left = room.position.left;
    hotspot.onclick = () => showRoomModal(room);
    hotspotContainer.appendChild(hotspot);
  });
}

// -------------------- Modal --------------------
let currentDbData = null;

async function showRoomModal(room) {
  currentRoom = room;
  const modal = document.getElementById('roomModal');
  const roomDetails = document.getElementById('roomDetails');
  roomDetails.innerHTML = '<div style="padding:40px;text-align:center;color:#94a3b8"><i class="bi bi-arrow-repeat spin"></i> Loading...</div>';
  modal.classList.add('active');

  // Fetch DB details
  let db = null;
  try {
    const url = `/api/floorplan/unit/${encodeURIComponent(room.project_name || '')}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) {
      roomDetails.innerHTML =
        '<div style="padding:32px 24px;text-align:center">' +
          `<p style="font-weight:600;font-size:1.1rem;color:#1e293b">Room ${room.unit_no}</p>` +
          '<p style="color:#b91c1c;font-size:0.88rem">Unit not found in database.</p>' +
        '</div>';
      return;
    }
    db = await res.json();
  } catch (e) {
    roomDetails.innerHTML = '<div style="padding:32px 24px;text-align:center;color:#b91c1c">Failed to load data.</div>';
    return;
  }

  currentDbData = db;

  const status = db?.process_status ?? (dbUnitStatusMap[room.project_name] || 'Available');
  const statusClass = status.toLowerCase();
  const area = db?.approximate_area;
  const areaText = area ? `${area} sqm` : '-';
  const storageBase = '/storage/';

  const imgSrc = db?.room_layout_image ? `${storageBase}${db.room_layout_image}` : null;

  // Build modal HTML
  let html = '';

  // Header
  html += '<div class="rm-header">';
  html += `<div class="rm-unit-code">${db?.unit_code || room.unit_no}</div>`;
  html += `<span class="rm-status ${statusClass}">${status}</span>`;
  html += '</div>';

  // Body: left details + right image
  html += '<div class="rm-body">';

  // Left: detail grid
  const details = [
    ['Unit Type', db?.unit_type ?? '-'],
    ['Bedrooms', db?.bedrooms ?? '-'],
    ['Area', areaText],
    ['Price', db?.price ? `฿ ${db.price}` : '-'],
    ['Price/SQM', db?.price_per_sqm ? `฿ ${db.price_per_sqm}` : '-'],
  ];

  html += '<div class="rm-details">';
  details.forEach(([label, value]) => {
    html += '<div class="rm-detail-item">';
    html += `<div class="rm-detail-label">${label}</div>`;
    html += `<div class="rm-detail-value">${value}</div>`;
    html += '</div>';
  });
  html += '</div>';

  // Right: image
  if (imgSrc) {
    html += '<div class="rm-image-wrap">';
    html += `<a href="${imgSrc}" target="_blank" title="View full image">`;
    html += `<img src="${imgSrc}" alt="Unit Type">`;
    html += '<div class="rm-image-zoom"><i class="bi bi-arrows-fullscreen"></i></div>';
    html += '</a>';
    html += '</div>';
  }

  html += '</div>'; // .rm-body

  // Quotation buttons — only for Available status
  if (db?.sale_id && statusClass === 'available') {
    html += '<div class="rm-actions">';
    html += `<button class="btn rm-btn-th" onclick="openFpQuotation('th')"><i class="bi bi-file-earmark-text me-1"></i>Quotation TH</button>`;
    html += `<button class="btn rm-btn-en" onclick="openFpQuotation('en')"><i class="bi bi-file-earmark-text me-1"></i>Quotation EN</button>`;
    html += '</div>';
  }

  roomDetails.innerHTML = html;
}

function closeModal() {
  document.getElementById('roomModal').classList.remove('active');
  currentRoom = null;
  currentDbData = null;
}

// -------------------- Quotation Visitor --------------------
function openFpQuotation(language) {
  if (!currentDbData) return;
  const fpModal = document.getElementById('fpQuotationModal');
  if (!fpModal) return;

  document.getElementById('fpQvSaleId').value = currentDbData.sale_id;
  document.getElementById('fpQvListingId').value = currentDbData.listing_id;
  document.getElementById('fpQvLanguage').value = language;
  document.getElementById('fpQvName').value = currentDbData.avail_name || '';
  document.getElementById('fpQvPhone').value = currentDbData.avail_tel || '';
  document.getElementById('fpQvName').classList.remove('is-invalid');
  document.getElementById('fpQvPhone').classList.remove('is-invalid');

  const bsModal = new bootstrap.Modal(fpModal);
  bsModal.show();
}

(function () {
  const submitBtn = document.getElementById('fpQvSubmitBtn');
  if (!submitBtn) return;

  const spinner = document.getElementById('fpQvSpinner');
  const nameInput = document.getElementById('fpQvName');
  const phoneInput = document.getElementById('fpQvPhone');
  const nameError = document.getElementById('fpQvNameError');
  const phoneError = document.getElementById('fpQvPhoneError');
  const saleIdInput = document.getElementById('fpQvSaleId');
  const languageInput = document.getElementById('fpQvLanguage');

  submitBtn.addEventListener('click', () => {
    let hasError = false;
    nameInput.classList.remove('is-invalid');
    phoneInput.classList.remove('is-invalid');

    if (!nameInput.value.trim()) {
      nameInput.classList.add('is-invalid');
      nameError.textContent = 'Please enter visitor name.';
      hasError = true;
    }
    if (!phoneInput.value.trim()) {
      phoneInput.classList.add('is-invalid');
      phoneError.textContent = 'Please enter phone number.';
      hasError = true;
    }
    if (hasError) return;

    submitBtn.disabled = true;
    spinner.classList.remove('d-none');

    fetch(`/buy-sale/${saleIdInput.value}/quotation-visitor`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        visitor_name: nameInput.value.trim(),
        visitor_phone: phoneInput.value.trim(),
        language: languageInput.value,
      }),
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Update cached data so next open shows new values
        if (currentDbData) {
          currentDbData.avail_name = nameInput.value.trim();
          currentDbData.avail_tel = phoneInput.value.trim();
        }
        window.open(data.redirect_url, '_blank');
        bootstrap.Modal.getInstance(document.getElementById('fpQuotationModal')).hide();
      } else {
        alert('Failed to save visitor information.');
      }
    })
    .catch(() => alert('An error occurred. Please try again.'))
    .finally(() => {
      submitBtn.disabled = false;
      spinner.classList.add('d-none');
    });
  });
})();

// -------------------- Init --------------------
loadData();