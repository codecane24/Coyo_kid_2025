
import React from 'react'
import ImageWithBasePath from '../../../../core/common/imageWithBasePath'
import { Link } from 'react-router-dom'

const DataTableStudentPrmotion = () => {
  return (


<div className=" cardhead">
  <div className="content1">
    <div className="row">
      <div className="col-12"> {/* changed from col-xl-6 to col-12 */}
        <div className="card w-100">
      
          <div className="card-body" style={{ overflowX: 'auto' }}>
            <div className="table-responsive">
              <table className="table text-nowrap table-sm" style={{ width: '100%' }}>
                <thead>
                  <tr>
                    <th scope="col">Student Name</th>
                    <th scope="col">Admission Number</th>
                
                  </tr>
                </thead>
                <tbody>
                  {[
                    { id: 'sm', name: 'Zelensky', date: '123456', status: 'Paid', statusClass: 'bg-soft-success' },
                    { id: 'sm1', name: 'Kim Jong', date: '223232', status: 'Pending', statusClass: 'bg-soft-danger' },
                    { id: 'sm2', name: 'Obana', date: '212522', status: 'Paid', statusClass: 'bg-soft-success' },
                    { id: 'sm3', name: 'Sean Paul', date: '2656', status: 'Paid', statusClass: 'bg-soft-success' },
                    { id: 'sm4', name: 'Karizma', date: '55485', status: 'Pending', statusClass: 'bg-soft-danger' },
                  ].map((item, index) => (
                    <tr key={index}>
                      <th scope="row">
                        <div className="form-check">
                          <input className="form-check-input" type="checkbox" id={`checkebox-${item.id}`} defaultChecked={index === 0} />
                          <label className="form-check-label" htmlFor={`checkebox-${item.id}`}>
                            {item.name}
                          </label>
                        </div>
                      </th>
                      <td>{item.date}</td>
                    
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



  )
}

export default DataTableStudentPrmotion ;