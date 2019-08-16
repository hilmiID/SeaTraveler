package com.example.hilmi.seatraveler.Activity;

import android.app.ProgressDialog;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.support.v4.app.ActivityCompat;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.View;
import android.widget.ImageButton;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.example.hilmi.seatraveler.Adapter.Adapter_pantai;
import com.example.hilmi.seatraveler.Model.pantai;
import com.example.hilmi.seatraveler.R;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.tasks.OnSuccessListener;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

public class HasilRekomendasiActivity extends AppCompatActivity {

    private RecyclerView recyclerView;
    private Adapter_pantai adapter;
    private ArrayList<pantai> list_data = new ArrayList<>();
    private LinearLayoutManager layoutManager;
    private ImageButton backButton;
    private String latitude, longitude, valueJarak, valueHTM, valueRating, valueFasilitas, valueTransportasi;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_hasil_rekomendasi);

        //get data from intent
        Intent intent = getIntent();
        latitude=intent.getExtras().getString("latitude");
        longitude=intent.getExtras().getString("longitude");
        valueJarak=intent.getExtras().getString("valueJarak");
        valueHTM=intent.getExtras().getString("valueHTM");
        valueRating=intent.getExtras().getString("valueRating");
        valueFasilitas=intent.getExtras().getString("valueFasilitas");
        valueTransportasi=intent.getExtras().getString("valueTransportasi");
        Log.e("latitude dan longitude", latitude+" dan "+longitude);

        //calling method getData for get JSON rekomendasi
        getData();

        //code for recycler view that showing result of recommendation
        recyclerView = (RecyclerView) findViewById(R.id.recycler);
        layoutManager = new LinearLayoutManager(HasilRekomendasiActivity.this, LinearLayoutManager.VERTICAL, false);
        adapter = new Adapter_pantai(HasilRekomendasiActivity.this,this.list_data);
        recyclerView.setLayoutManager(layoutManager);
        recyclerView.setHasFixedSize(false);
        recyclerView.setAdapter(adapter);

        //press back button
        backButton = findViewById(R.id.backButton);
        backButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });
    }

    public void getData(){
        final ProgressDialog progressDialog = new ProgressDialog(HasilRekomendasiActivity.this);
        progressDialog.setMessage("Loading...");
        progressDialog.show();

        RequestQueue requestQueue = Volley.newRequestQueue(HasilRekomendasiActivity.this);
        String domain = getResources().getString(R.string.url);
        String url = domain+"pantai_gps.php";
        Log.e("Isi Get Data",url);
        StringRequest stringRequest = new StringRequest(Request.Method.POST, url, new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    Log.e("tagconvertstr", "["+response+"]");
                    JSONObject jsonObject = new JSONObject(response);
                    JSONArray jsonArray = jsonObject.getJSONArray("rekomendasi_pantai");
                    for (int a = 0; a < jsonArray.length(); a ++){
                        JSONObject json = jsonArray.getJSONObject(a);
                        pantai model = new pantai();
                        model.setId_pantai(json.getString("id_pantai"));
                        model.setNama_pantai(json.getString("nama_pantai"));
                        model.setBiaya_masuk(json.getString("biaya_masukNonTOPSIS"));
                        model.setRating(json.getString("ratingNonTOPSIS"));
                        model.setTransportasi(json.getString("transportasiNonTOPSIS"));
                        model.setFasilitas(json.getString("fasilitasNonTOPSIS"));
                        model.setJarak(json.getString("jarakNonTOPSIS"));
                        model.setDplus(json.getString("Dplus"));
                        model.setDminus(json.getString("Dminus"));
                        model.setJarak_solusi(json.getString("jarak_solusi"));
                        model.setLatitude(json.getString("latitude"));
                        model.setLongitude(json.getString("longitude"));
                        model.setImage(json.getString("image"));
                        model.setDeskripsi(json.getString("deskripsi"));
                        model.setAlamat(json.getString("alamat"));
                        model.setMyLatitude(latitude);
                        model.setMyLongitude(longitude);
                        list_data.add(model);
                    }
                    Log.e("json", jsonObject.getString("rekomendasi_pantai"));
                    Log.e("try1", String.valueOf(list_data.get(0).getRating()));
                } catch (JSONException e) {
                    e.printStackTrace();
                    Log.e("Error json", e.getMessage());
                    progressDialog.dismiss();
                }
                adapter.notifyDataSetChanged();
                progressDialog.dismiss();
            }
        }, new Response.ErrorListener() {

            @Override
            public void onErrorResponse(VolleyError error) {
                Toast.makeText(HasilRekomendasiActivity.this, error.getMessage(), Toast.LENGTH_SHORT).show();
                Log.e("Error valley","Volley");
                progressDialog.dismiss();
            }
        }) {
            @Override
            protected Map<String, String> getParams() {
                // Posting parameters
                Map<String, String> params = new HashMap<String, String>();
                params.put("latitude", latitude);
                params.put("longitude", longitude);
                params.put("valueJarak", valueJarak);
                params.put("valueHTM", valueHTM);
                params.put("valueRating", valueRating);
                params.put("valueFasilitas", valueFasilitas);
                params.put("valueTransportasi", valueTransportasi);

                return params;
            }
        };

        requestQueue.add(stringRequest);

    }
}
